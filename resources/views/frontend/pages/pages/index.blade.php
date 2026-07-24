@extends('frontend.layouts.app')
@section('title', $page['title'])

@section('meta-title', $page['title'])
@section('meta-type', 'article')
@section('meta-url', route('pages.index',['id'=>$page['id'], 'slug'=> $page['slug']]))
@section('meta-description', Str::limit($page['content'], 120))

@section('content')
    <div class="card">
        <div class="card-header">
            <h1>{{$page['title']}}</h1>
        </div>
        <div class="card-body">
            {!! nl2br($page['content']) !!}
        </div>
    </div>
@endsection
