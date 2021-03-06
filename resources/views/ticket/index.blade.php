@extends('layout')

@section('title', trans('title.ticket'))

@section('content')
    <div class="box">
        <div class="ui primary attached message">
            <h1 class="header"><i class="ticket icon"></i>@yield('title')</h1>
        </div>
        <div class="ui attached fluid clearing segment">
            <section class="ui clearing segment">
                <h3 class="ui dividing header">@lang('shop.search.header')</h3>
                <form class="ui form" action="{{ route('ticket.index') }}" method="GET">
                    <div class="three fields">
                        <div class="field">
                            <label for="title">@lang('ticket.index.search_section.title')</label>
                            <input type="text" name="title" id="title" value="{{ request('title') }}">
                        </div>
                        <div class="field">
                            <label for="creation_date_min">@lang('ticket.index.search_section.creation_date_min')</label>
                            <div class="ui calendar date-picker">
                                <div class="ui input left icon">
                                    <i class="time icon"></i>
                                    <input type="text" name="creation_date_min" id="creation_date_min" value="{{ request('creation_date_min') }}">
                                </div>
                            </div>
                        </div>
                        <div class="field">
                            <label for="creation_date_max">@lang('ticket.index.search_section.creation_date_max')</label>
                            <div class="ui calendar date-picker">
                                <div class="ui input left icon">
                                    <i class="time icon"></i>
                                    <input type="text" name="creation_date_max" id="creation_date_max" value="{{ request('creation_date_max') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="two fields">
                        <div class="field">
                            <label for="categories">@lang('ticket.index.search_section.categories')</label>
                            <select multiple="" class="ui dropdown" name="categories[]" id="categories">
                                <option value="">@lang('ticket.index.search_section.select_categories')</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ collect(request('categories'))->contains($category->id) ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label for="statuses">@lang('ticket.index.search_section.statuses')</label>
                            <select multiple="" class="ui dropdown" name="statuses[]" id="statuses">
                                <option value="">@lang('ticket.index.search_section.select_statuses')</option>
                                @foreach($statuses as $status)
                                    <option value="{{ $status }}" {{ collect(request('statuses'))->contains($status) ? 'selected' : '' }}>@lang('ticket.statuses.' . $status)</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <button class="ui primary right floated right labeled icon button" type="submit"><i class="right arrow icon"></i>@lang('shop.search.submit')</button>
                    <button id="reset_form" class="ui orange right floated right labeled icon button"><i class="remove icon"></i>@lang('shop.search.clear_form')</button>
                </form>
            </section>

            <h3 class="ui dividing header">
                @lang('ticket.index.ticket_list')
                <a href="{{ route('ticket.create') }}" class="ui label right floated">
                    <i class="add icon"></i>@lang('ticket.index.create_ticket')
                </a>
            </h3>
            <table class="ui single line compact selectable table">
                <thead>
                    <tr>
                        <th>@lang('ticket.index.title')</th>
                        <th>@lang('ticket.index.category')</th>
                        <th>@lang('ticket.index.status')</th>
                        <th>@lang('ticket.index.creation_date')</th>
                        <th>@lang('ticket.index.action')</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tickets as $ticket)
                        <tr>
                            <td>{{ $ticket->title }}</td>
                            <td>{{ $ticket->category->name }}</td>
                            <td>{{ $ticket->status_label }}</td>
                            <td><time datetime="{{ $ticket->created_at }}">{{ $ticket->created_at }}</time></td>
                            <td>
                                <a href="{{ route('ticket.show', [$ticket]) }}" class="ui primary compact button icon"><i class="eye icon"></i></a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="ui divider"></div>
            {{ $tickets->appends(request()->except('page'))->links() }}
        </div>
    </div>
@endsection

<?php /** @var \App\Model\Web\Ticket $ticket */ ?>