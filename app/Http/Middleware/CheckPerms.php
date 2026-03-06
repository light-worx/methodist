<?php

namespace App\Http\Middleware;

use App\Models\Circuit;
use App\Models\Meeting;
use App\Models\Person;
use App\Models\Preacher;
use App\Models\Service;
use App\Models\Society;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CheckPerms
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        // Super admins bypass all checks
        if ($user->hasRole('super_admin')) {
            return $next($request);
        }

        // Route to appropriate permission check
        if (str_contains($request->path(), 'circuits')) {
            $this->checkCircuitPermission($request, $user);
        } elseif (str_contains($request->path(), 'societies')) {
            $this->checkSocietyPermission($request, $user);
        } elseif (str_contains($request->path(), 'people')) {
            $this->checkPersonPermission($request, $user);
        } elseif (str_contains($request->path(), 'preachers')) {
            $this->checkPreacherPermission($request, $user);
        } elseif (str_contains($request->path(), 'services')) {
            $this->checkServicePermission($request, $user);
        } elseif (str_contains($request->path(), 'meetings')) {
            $this->checkMeetingPermission($request, $user);
        } elseif (str_contains($request->path(), 'users')) {
            $this->checkUserPermission($request, $user);
        }
        return $next($request);
    }

    protected function checkUserPermission(Request $request, $user): void
    {
        $userId = $this->extractIdFromPath($request->path(), 'users');
        
        // If no ID (list view), check if user has any permissions
        if (!$userId) {
            if (!$user->districts && !$user->circuits && !$user->societies) {
                abort(Response::HTTP_FORBIDDEN);
            }
            return;
        }
        
        // Users can always edit themselves
        if ($userId === $user->id) {
            return;
        }
        
        $targetUser = \App\Models\User::findOrFail($userId);
        
        // District-level users can access anyone in their districts
        if ($user->districts) {
            // Check if target has districts in common
            if ($targetUser->districts) {
                $hasOverlap = !empty(array_intersect($user->districts, $targetUser->districts));
                if ($hasOverlap) {
                    return;
                }
            }
            
            // Check if ALL target's circuits belong to user's districts
            if ($targetUser->circuits) {
                $targetCircuitsInUserDistricts = Circuit::whereIn('district_id', $user->districts)
                    ->whereIn('id', $targetUser->circuits)
                    ->pluck('id')
                    ->toArray();
                    
                if (count($targetCircuitsInUserDistricts) === count($targetUser->circuits)) {
                    return;
                }
            }
            
            // Check if ALL target's societies belong to user's districts
            if ($targetUser->societies) {
                $targetSocietiesInUserDistricts = Society::whereHas('circuit', function ($query) use ($user) {
                    $query->whereIn('district_id', $user->districts);
                })->whereIn('id', $targetUser->societies)
                ->pluck('id')
                ->toArray();
                
                if (count($targetSocietiesInUserDistricts) === count($targetUser->societies)) {
                    return;
                }
            }
        }
        
        // Circuit-level users can only access users whose permissions are ALL within their circuits
        if ($user->circuits) {
            // No access if target has district permissions
            if ($targetUser->districts) {
                abort(Response::HTTP_FORBIDDEN);
            }
            
            // Check if ALL target's circuits overlap
            if ($targetUser->circuits) {
                $hasOverlap = !empty(array_intersect($user->circuits, $targetUser->circuits));
                $allCircuitsMatch = empty(array_diff($targetUser->circuits, $user->circuits));
                
                if ($hasOverlap && $allCircuitsMatch) {
                    return;
                }
            }
            
            // Check if ALL target's societies belong to user's circuits
            if ($targetUser->societies) {
                $targetSocietiesInUserCircuits = Society::whereIn('circuit_id', $user->circuits)
                    ->whereIn('id', $targetUser->societies)
                    ->pluck('id')
                    ->toArray();
                    
                if (count($targetSocietiesInUserCircuits) === count($targetUser->societies)) {
                    return;
                }
            }
        }
        
        // Society-level users can only access users with the same societies
        if ($user->societies) {
            // No access if target has district or circuit permissions
            if ($targetUser->districts || $targetUser->circuits) {
                abort(Response::HTTP_FORBIDDEN);
            }
            
            // Check if ALL target's societies overlap
            if ($targetUser->societies) {
                $hasOverlap = !empty(array_intersect($user->societies, $targetUser->societies));
                $allSocietiesMatch = empty(array_diff($targetUser->societies, $user->societies));
                
                if ($hasOverlap && $allSocietiesMatch) {
                    return;
                }
            }
        }
        
        abort(Response::HTTP_FORBIDDEN);
    }

    protected function checkCircuitPermission(Request $request, $user): void
    {
        $circuitId = $this->extractIdFromPath($request->path(), 'circuits');
        
        // If no ID (list view), check if user has any circuit-related permissions
        if (!$circuitId) {
            if (!$user->districts && !$user->circuits && !$user->societies) {
                abort(Response::HTTP_FORBIDDEN);
            }
            return;
        }
        
        // Check district-level access
        if ($user->districts) {
            $hasAccess = Circuit::whereIn('district_id', $user->districts)
                ->where('id', $circuitId)
                ->exists();
                
            if ($hasAccess) {
                return;
            }
        }
        
        // Check direct circuit access
        if ($user->circuits && in_array($circuitId, $user->circuits)) {
            return;
        }
        
        // Check society-level access (can view circuits their societies belong to)
        if ($user->societies) {
            $hasAccess = Society::whereIn('id', $user->societies)
                ->where('circuit_id', $circuitId)
                ->exists();
                
            if ($hasAccess) {
                return;
            }
        }
        
        abort(Response::HTTP_FORBIDDEN);
    }

    protected function checkSocietyPermission(Request $request, $user): void
    {
        $societyId = $this->extractIdFromPath($request->path(), 'societies');
        
        // If no ID (list view), check if user has any society-related permissions
        if (!$societyId) {
            if (!$user->districts && !$user->circuits && !$user->societies) {
                abort(Response::HTTP_FORBIDDEN);
            }
            return;
        }
        
        // Check direct society access
        if ($user->societies && in_array($societyId, $user->societies)) {
            return;
        }
        
        // Check circuit-level access
        if ($user->circuits) {
            $hasAccess = Society::whereIn('circuit_id', $user->circuits)
                ->where('id', $societyId)
                ->exists();
                
            if ($hasAccess) {
                return;
            }
        }
        
        // Check district-level access
        if ($user->districts) {
            $hasAccess = Society::whereHas('circuit', function ($query) use ($user) {
                $query->whereIn('district_id', $user->districts);
            })->where('id', $societyId)->exists();
            
            if ($hasAccess) {
                return;
            }
        }
        
        abort(Response::HTTP_FORBIDDEN);
    }

    protected function checkPersonPermission(Request $request, $user): void
    {
        $personId = $this->extractIdFromPath($request->path(), 'people');
        
        // If no ID (list view), check if user has any permissions
        if (!$personId) {
            if (!$user->districts && !$user->circuits && !$user->societies) {
                abort(Response::HTTP_FORBIDDEN);
            }
            return;
        }
        
        $person = Person::findOrFail($personId);
        
        // Check circuit assignments (for ministers via pivot table)
        if ($user->circuits) {
            $hasAccess = $person->circuits()
                ->whereIn('circuit_id', $user->circuits)
                ->exists();
                
            if ($hasAccess) {
                return;
            }
        }
        
        // Check district-level access via circuit assignments
        if ($user->districts) {
            $hasAccess = $person->circuits()
                ->whereIn('district_id', $user->districts)
                ->exists();
                
            if ($hasAccess) {
                return;
            }
        }
        
        abort(Response::HTTP_FORBIDDEN);
    }

    protected function checkPreacherPermission(Request $request, $user): void
    {
        $preacherId = $this->extractIdFromPath($request->path(), 'preachers');
        
        // If no ID (list view), check if user has any permissions
        if (!$preacherId) {
            if (!$user->districts && !$user->circuits && !$user->societies) {
                abort(Response::HTTP_FORBIDDEN);
            }
            return;
        }
        
        $preacher = Preacher::with('person')->findOrFail($preacherId);
        
        // Preacher must have an associated person
        if (!$preacher->person) {
            abort(Response::HTTP_FORBIDDEN);
        }
        
        $person = $preacher->person;
        
        // Check direct society access
        if ($preacher->society_id && $user->societies && in_array($preacher->society_id, $user->societies)) {
            return;
        }
        
        // Check circuit-level access
        if ($preacher->society_id && $user->circuits) {
            $hasAccess = Society::whereIn('circuit_id', $user->circuits)
                ->where('id', $preacher->society_id)
                ->exists();
                
            if ($hasAccess) {
                return;
            }
        }
        
        // Check district-level access
        if ($preacher->society_id && $user->districts) {
            $hasAccess = Society::whereHas('circuit', function ($query) use ($user) {
                $query->whereIn('district_id', $user->districts);
            })->where('id', $preacher->society_id)->exists();
            
            if ($hasAccess) {
                return;
            }
        }
        
        abort(Response::HTTP_FORBIDDEN);
    }

    protected function checkServicePermission(Request $request, $user): void
    {
        $serviceId = $this->extractIdFromPath($request->path(), 'services');
        
        // If no ID (list view), check if user has any permissions
        if (!$serviceId) {
            if (!$user->districts && !$user->circuits && !$user->societies) {
                abort(Response::HTTP_FORBIDDEN);
            }
            return;
        }
        
        $service = Service::findOrFail($serviceId);
        
        // Check direct society access
        if ($user->societies && in_array($service->society_id, $user->societies)) {
            return;
        }
        
        // Check circuit-level access
        if ($user->circuits) {
            $hasAccess = Society::whereIn('circuit_id', $user->circuits)
                ->where('id', $service->society_id)
                ->exists();
                
            if ($hasAccess) {
                return;
            }
        }
        
        // Check district-level access
        if ($user->districts) {
            $hasAccess = Society::whereHas('circuit', function ($query) use ($user) {
                $query->whereIn('district_id', $user->districts);
            })->where('id', $service->society_id)->exists();
            
            if ($hasAccess) {
                return;
            }
        }
        
        abort(Response::HTTP_FORBIDDEN);
    }

    protected function checkMeetingPermission(Request $request, $user): void
    {
        $meetingId = $this->extractIdFromPath($request->path(), 'meetings');
        
        // If no ID (list view), check if user has any permissions
        if (!$meetingId) {
            if (!$user->districts && !$user->circuits && !$user->societies) {
                abort(Response::HTTP_FORBIDDEN);
            }
            return;
        }
        
        $meeting = Meeting::findOrFail($meetingId);
        
        // Check direct circuit access
        if ($user->circuits && in_array($meeting->circuit_id, $user->circuits)) {
            return;
        }
        
        // Check district-level access
        if ($user->districts) {
            $hasAccess = Circuit::whereIn('district_id', $user->districts)
                ->where('id', $meeting->circuit_id)
                ->exists();
                
            if ($hasAccess) {
                return;
            }
        }
        
        // Check society-level access (can view circuit meetings if they belong to a society in that circuit)
        if ($user->societies) {
            $hasAccess = Society::whereIn('id', $user->societies)
                ->where('circuit_id', $meeting->circuit_id)
                ->exists();
                
            if ($hasAccess) {
                return;
            }
        }
        
        abort(Response::HTTP_FORBIDDEN);
    }

    protected function extractIdFromPath(string $path, string $resource): ?int
    {
        // Extract ID from paths like "admin/circuits/123/edit" or "admin/circuits/123"
        preg_match("/\/{$resource}\/(\d+)/", $path, $matches);
        return isset($matches[1]) ? (int) $matches[1] : null;
    }
}