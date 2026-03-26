@extends('vendor.pwa.layouts.app')

@section('content')
    <h3>Lectionary readings</h3>
    <livewire:service-details :service="$lects" />
@endsection