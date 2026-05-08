@extends('layouts.admin')

@section('title', 'Birthday Settings')

@section('breadcrumb')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Birthday Settings</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"> <i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item">HR</li>
                    <li class="breadcrumb-item"><a href="{{ route('birthdays.index') }}">Birthdays</a></li>
                    <li class="breadcrumb-item active">Settings</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Notification Configuration</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('birthdays.settings.update') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="birthday_notification_enabled">Enable Notifications</label>
                                    <div class="media-body icon-state">
                                        <label class="switch">
                                            <input type="checkbox" id="birthday_notification_enabled" name="birthday_notification_enabled" {{ $settings['birthday_notification_enabled'] == '1' ? 'checked' : '' }}><span class="switch-state"></span>
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">Toggle to enable or disable automatic birthday notifications.</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="birthday_notification_time">Notification Time</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa fa-clock-o"></i></span>
                                        <input class="form-control" id="birthday_notification_time" type="time" name="birthday_notification_time" value="{{ $settings['birthday_notification_time'] }}" required>
                                    </div>
                                    <small class="form-text text-muted">The time when notifications will be sent daily.</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="birthday_notification_message">Employee Notification Message</label>
                                    <textarea class="form-control" id="birthday_notification_message" name="birthday_notification_message" rows="4" required>{{ $settings['birthday_notification_message'] }}</textarea>
                                    <small class="form-text text-muted">Use <code>{name}</code> for employee name, <code>{age}</code> for turning age.</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="birthday_hr_notification_message">HR Notification Message</label>
                                    <textarea class="form-control" id="birthday_hr_notification_message" name="birthday_hr_notification_message" rows="4" required>{{ $settings['birthday_hr_notification_message'] }}</textarea>
                                    <small class="form-text text-muted">Use <code>{name}</code> for employee name, <code>{age}</code> for turning age.</small>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save me-2"></i>Save Settings
                                </button>
                                <a href="{{ route('birthdays.index') }}" class="btn btn-secondary">
                                    <i class="fa fa-times me-2"></i>Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection