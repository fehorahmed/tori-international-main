@extends('layouts.app')
@section('title','HRM Settings')
@section('content')
    <div class="row">
        <div class="col-md-6 m-auto">
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Attendance Notification Settings</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('hrm-settings.update') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="notification_interval_minutes">Notification Interval (Minutes) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="notification_interval_minutes" id="notification_interval_minutes" 
                                    class="form-control @error('notification_interval_minutes') is-invalid @enderror" 
                                    value="{{ old('notification_interval_minutes', $setting->notification_interval_minutes) }}" 
                                    min="1" required>
                                <div class="input-group-append">
                                    <span class="input-group-text">Minute(s)</span>
                                </div>
                                @error('notification_interval_minutes')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <small class="form-text text-muted">How often should the system send an attendance reminder to employees.</small>
                        </div>

                        <div class="form-group mt-3">
                            <label for="response_window_minutes">Response Window (Minutes) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="response_window_minutes" id="response_window_minutes" 
                                    class="form-control @error('response_window_minutes') is-invalid @enderror" 
                                    value="{{ old('response_window_minutes', $setting->response_window_minutes) }}" 
                                    min="1" required>
                                <div class="input-group-append">
                                    <span class="input-group-text">Minute(s)</span>
                                </div>
                                @error('response_window_minutes')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <small class="form-text text-muted">How much time an employee has to respond after receiving the reminder.</small>
                        </div>

                        <div class="form-group mt-3">
                            <label>Web Notification (In-App Bell Icon)</label>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_web_notification_enabled" name="is_web_notification_enabled" {{ old('is_web_notification_enabled', $setting->is_web_notification_enabled ?? true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_web_notification_enabled">Enable Web Notifications</label>
                            </div>
                        </div>

                        <div class="form-group mt-3">
                            <label>Browser Notification (Web Push)</label>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_browser_notification_enabled" name="is_browser_notification_enabled" {{ old('is_browser_notification_enabled', $setting->is_browser_notification_enabled ?? true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_browser_notification_enabled">Enable Native Browser Notifications</label>
                            </div>
                        </div>

                        <div class="form-group mt-3">
                            <label>Push Notification (Android/FCM)</label>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_push_notification_enabled" name="is_push_notification_enabled" {{ old('is_push_notification_enabled', $setting->is_push_notification_enabled ?? true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_push_notification_enabled">Enable Push Notifications</label>
                            </div>
                        </div>

                        <hr class="mt-4 mb-4">
                        <h5 class="text-primary"><i class="fas fa-fire text-warning mr-2"></i>Firebase Cloud Messaging (FCM) Credentials</h5>
                        <p class="text-muted small">These credentials are used for both Web Push and Android Push Notifications.</p>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fcm_api_key">API Key</label>
                                    <input type="text" name="fcm_api_key" id="fcm_api_key" class="form-control" value="{{ old('fcm_api_key', $setting->fcm_api_key ?? '') }}" placeholder="AIzaSy...">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fcm_auth_domain">Auth Domain</label>
                                    <input type="text" name="fcm_auth_domain" id="fcm_auth_domain" class="form-control" value="{{ old('fcm_auth_domain', $setting->fcm_auth_domain ?? '') }}" placeholder="your-project.firebaseapp.com">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fcm_project_id">Project ID</label>
                                    <input type="text" name="fcm_project_id" id="fcm_project_id" class="form-control" value="{{ old('fcm_project_id', $setting->fcm_project_id ?? '') }}" placeholder="your-project-1234">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fcm_storage_bucket">Storage Bucket</label>
                                    <input type="text" name="fcm_storage_bucket" id="fcm_storage_bucket" class="form-control" value="{{ old('fcm_storage_bucket', $setting->fcm_storage_bucket ?? '') }}" placeholder="your-project.appspot.com">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fcm_messaging_sender_id">Messaging Sender ID</label>
                                    <input type="text" name="fcm_messaging_sender_id" id="fcm_messaging_sender_id" class="form-control" value="{{ old('fcm_messaging_sender_id', $setting->fcm_messaging_sender_id ?? '') }}" placeholder="1234567890">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fcm_app_id">App ID</label>
                                    <input type="text" name="fcm_app_id" id="fcm_app_id" class="form-control" value="{{ old('fcm_app_id', $setting->fcm_app_id ?? '') }}" placeholder="1:1234567890:web:abcdef">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fcm_measurement_id">Measurement ID (Optional)</label>
                                    <input type="text" name="fcm_measurement_id" id="fcm_measurement_id" class="form-control" value="{{ old('fcm_measurement_id', $setting->fcm_measurement_id ?? '') }}" placeholder="G-ABCDEF">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="fcm_server_key">Secret Server Key (Legacy) / Service Account Key <span class="text-danger">*</span></label>
                                    <input type="password" name="fcm_server_key" id="fcm_server_key" class="form-control" value="{{ $setting->fcm_server_key ? '********' : '' }}" placeholder="Enter server key to update...">
                                    <small class="form-text text-muted">For security reasons, the current key is hidden. Only fill this if you want to update it.</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-4 text-center">
                            <button type="submit" class="btn btn-info bg-gradient-info px-4">
                                <i class="fas fa-save mr-1"></i> Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
