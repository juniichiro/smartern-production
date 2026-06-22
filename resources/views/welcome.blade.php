@extends('layouts.guest')

@section('title', 'SmarTern')

@section('content')
    <x-landing.navbar />
    <x-landing.hero />
    <x-landing.about />
    <x-landing.how-it-works />
    <x-landing.faq />
    <x-landing.cta />
    <x-landing.footer />
@endsection
