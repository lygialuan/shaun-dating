<header class="modal-card-head">
    <p class="modal-card-title">{{ $title }}</p>
</header>

<div id="form_import_users" style="{{ $job ? 'display:none' : '' }}">
    <section class="modal-card-body">
        <div class="card-content">
            <p class="admin-card-help">{{__('Enter your existing database credentials to begin importing users.')}}</p>
            <form id="migrate_old_dating_form" method="post" action="{{ route('admin.migrate_old_dating.store') }}" onsubmit="return false;">
                <div id="errors"></div>
                {{ csrf_field() }}
                <div class="form-group">
                    <label class="control-label">
                        {{ __('Database Host (e.g. localhost, IP)') }}
                    </label>
                    <input class="form-control" name="database_host" type="text">
                </div>
                <div class="form-group">
                    <label class="control-label">
                        {{ __('Port (default: 3306)') }}
                    </label>
                    <input class="form-control" name="port" type="number">
                </div>
                <div class="form-group">
                    <label class="control-label">
                        {{ __('Database Name') }}
                    </label>
                    <input class="form-control" name="database_name" type="text">
                </div>
                <div class="form-group">
                    <label class="control-label">
                        {{ __('Username') }}
                    </label>
                    <input class="form-control" name="user_name" type="text">
                </div>
                <div class="form-group">
                    <label class="control-label">
                        {{ __('Password') }}
                    </label>
                    <input class="form-control" name="password" type="text">
                </div>
            </form>
        </div>
    </section>
    <footer class="modal-card-foot">
        <button class="btn-filled-blue" id="migrate_old_dating_submit">
            <span class="btn-text">{{ __('Test Connection') }}</span>
            <span class="btn-loading" style="display:none;">{{ __('Loading...') }}</span>
        </button>
        <button class="btn-filled-white modal-close">
            {{ __('Cancel') }}
        </button>
    </footer>
</div>
<div id="confirm_import_users" style="{{ $job ? '' : 'display:none' }}">
    <section class="modal-card-body">
        <div class="card-content">
            <p class="admin-card-help" id="text_not_yet_import" style="{{ $job && $job->status == 'pending' ? '' : 'display:none' }}">{{__('Connect successfully. Please click on "Import Now" To start importing.')}}</p>
            <p class="admin-card-help" id="text_imported" style="{{ $job && $job->status == 'pending' ? 'display:none' : '' }}">    {{ __(':total users has been imported.', ['total' => $job?->total]) }}</p>
        </div>
    </section>
    <footer class="modal-card-foot">
        <button class="btn-filled-blue" id="remove_connection" data-url="{{ route('admin.migrate_old_dating.remove') }}">
            {{ __('Remove Connection') }}
        </button>
        <button class="btn-filled-blue" id="import_now" data-url="{{ route('admin.migrate_old_dating.import') }}" style="{{ $job && $job->status == 'pending' ? '' : 'display:none' }}">
            {{ __('Import Now') }}
        </button>
        <button class="btn-filled-white modal-close">
            {{ __('Cancel') }}
        </button>
    </footer>
</div>
<script>
      adminMigrateOldDating.initCreate();
      adminMigrateOldDating.bindImportNow();
      adminMigrateOldDating.bindRemoveConnection();
</script>