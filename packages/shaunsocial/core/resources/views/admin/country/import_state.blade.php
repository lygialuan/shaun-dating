<header class="modal-card-head">
    <p class="modal-card-title">{{__('Import States')}}</p>
</header>
<section class="modal-card-body">        
    <div class="card-content">
        <form id="import_state_form" enctype="multipart/form-data" method="post" action="{{ route('admin.country.state.store_import', $countryId)}}" onsubmit="return false;">
            <div id="errors"></div>
            {{ csrf_field() }}
            <input type="hidden" name="country_id" value="{{ $countryId }}" class="form-control"/>
            <div class="form-group">
                <label class="control-label">{{__('File CSV')}}</label>
                <input class="form-control" name="file" type="file">
                <div class="padding-top-5"><a href="{{ asset('files/state_example.csv') }}">{{__('Download sample csv')}}</a></div>
            </div>
        </form>
    </div>
</section>
<footer class="modal-card-foot">
    <button class="btn-filled-blue" id="import_state_file">{{__('Import')}}</button>
    <button class="btn-filled-white modal-close">{{__('Cancel')}}</button>
</footer>
<script>
    adminCountry.initUploadStatesFile();
</script>
