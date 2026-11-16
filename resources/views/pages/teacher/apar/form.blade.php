@extends('layouts.app')

@section('content')
    <livewire:teacher.apar-form :academic-year="$academicYear" :submission-id="$submissionId" />
@endsection
