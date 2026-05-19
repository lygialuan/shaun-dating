@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{__('Customize your pricing plans')}}</h5>
    </div>    
    <div class="admin-card-body">
        <form id="pricing_table_form" method="post" action="{{ route('admin.user_subscription.store_pricing_table')}}">
            {{ csrf_field() }}
            <p class="admin-card-sub-title">{{__('Packages ( Selecting 3 packages tends to get better results.)')}}</p>
            <div id="compare_packages_list" class="mb-4">
                @foreach ($packages as $package)
                    <div class="form-check" data-id="{{$package->id}}">
                        <input class="form-check-input package-checkbox" type="checkbox" name="selected_packages[]" id="{{$package->id}}" value="{{$package->id}}" @if ($package->checkShow()) checked @endif>
                        <label class="form-check-label" for="{{$package->id}}">{{$package->name}}</label>
                    </div>
                @endforeach
            </div>
            <p class="admin-card-sub-title">{{__('Display Settings')}}</p>
            <div class="mb-4">
                <div class="mb-2">
                    <label for="highlight_background_color" class="form-label">{{__('Highlight background color')}}</label>
                    <input type="color" name="highlight_background_color" id="highlightedBackgroundColor" class="form-control form-control-color" value="{{$highlightBadge['highlight_background_color']}}">
                </div>
                <div class="mb-2">
                    <label for="highlight_text_color" class="form-label">{{__('Highlight text color')}}</label>
                    <input type="color" name="highlight_text_color" id="highlightedTextColor" class="form-control form-control-color" value="{{$highlightBadge['highlight_text_color']}}">
                </div>
                <div>
                    <label class="form-label">{{__('Highlight as')}}</label>
                    <select name="highlight_as" class="form-select mb-1" id="highlightedBadgeSelect">
                        @foreach ($highlightBadgesList as $value => $highlightBadge)
                            <option @if ($value == $highlightAs) selected @endif value="{{$value}}">{{$highlightBadge}}</option>
                        @endforeach
                    </select>
                    <select name="highlighted_package" class="form-select" id="highlightedPackageSelect">
                        @foreach ($packages as $package)
                            <option @if ($package->is_highlight) selected @endif value="{{$package->id}}">{{$package->name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <p class="admin-card-sub-title">{{__('Items in the compare table settings')}}</p>
            @if (count($packageCompares) > 0)
                <div class="form-group">
                    <label class="control-label">{{__('Language')}}</label>
                        <select id="language" name="language" class="form-select">
                            @foreach($languages as $item)
                                <option @if ($item->key == $language) selected @endif value="{{$item->key}}">{{$item->name}}</option>
                            @endforeach
                        </select>
                    <p class="help">
                        {{__('Select a language to translate for below compare fields, compare values.')}}
                    </p>
                </div>
            @endif
            <div class="admin-card-body table-responsive">
                <table class="table table-bordered" style="table-layout: fixed">
                    <thead>
                        <tr id="compareTableHeader">
                            <th width="200">{{ __('Features') }}</th>
                            @foreach ($packages as $package)
                                <th width="200" class="text-center package-column" data-id="{{$package->id}}" style="display: none;">
                                    {{$package->name}}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody id="table-body">
                        <tr class="compare-row compare-row_blank" name="sc[0]">
                            <td>
                                <div class="d-flex align-items-center gap-4 justify-content-between">
                                    <div class="flex-1">
                                        <input type="text" name="sc[0][name]" value="" class="form-name form-control" maxlength="255">
                                        <input type="hidden" name="sc[0][id]" value="0" class="form-id">
                                    </div>
                                    <span class="material-symbols-outlined notranslate compare-row-icon" role="button" onclick="$.rowCompare.remove($(this))"> delete </span>
                                </div>
                            </td>
                            @foreach ($packages as $package)
                                <td class="compare-row-column" data-id="{{$package->id}}">
                                    <input type="hidden" class="form-control" name="sc[0][scc][{{$package->id}}][id]" value="0">
                                    <input type="hidden" class="form-control compare_type" name="sc[0][scc][{{$package->id}}][type]" value="text">
                                    <input type="hidden" class="form-control boolean_value" name="sc[0][scc][{{$package->id}}][boolean_value]" value="1">
                                    <div class="d-flex align-items-center gap-4 justify-content-between">
                                        <div class="flex-1">
                                            <span class="material-symbols-outlined notranslate compare-row-icon type_boolean type_yes compare_type_value" role="button" title="<?php echo __('Yes');?>" onclick="$.rowCompare.switchYesNo($(this), 0)"> check_circle </span>
                                            <span class="material-symbols-outlined notranslate compare-row-icon type_boolean type_no compare_type_value" role="button" title="<?php echo __('No');?>" onclick="$.rowCompare.switchYesNo($(this), 1)"> cancel </span>
                                            <input type="text" class="form-control type_text compare_type_value" name="sc[0][scc][{{$package->id}}][text_value]" value=""  maxlength="255">
                                        </div>
                                        <span class="material-symbols-outlined notranslate compare-row-icon" role="button" onclick="$.rowCompare.switchType($(this))"> sync </span>
                                    </div>
                                </td>
                            @endforeach
                        </tr>
                        @foreach ($packageCompares as $key => $packageCompare)
                            <tr class="compare-row" id="compareTableHeader-{{$packageCompare->id}}" data-id="{{$packageCompare->id}}" name="sc[{{$key + 1}}]">
                                <td>
                                    <div class="d-flex align-items-center gap-4 justify-content-between">
                                        <div class="flex-1">
                                            <input type="text" name="sc[{{$key + 1}}][name]" value="{{$packageCompare->getTranslatedAttributeValue('name', $language)}}" class="form-control" maxlength="255">
                                            <input type="hidden" name="sc[{{$key + 1}}][id]" value="{{$packageCompare->id}}">
                                        </div>
                                        <span class="material-symbols-outlined notranslate compare-row-icon" role="button" onclick="$.rowCompare.remove($(this))"> delete </span>
                                    </div>
                                </td>
                                @foreach ($packages as $package)
                                    <?php 
                                    $column = $packageCompare->getColumn($package->id);
                                    ?>
                                    <td class="compare-row-column" data-id="{{$package->id}}">
                                        <input type="hidden" class="form-control" name="sc[{{$key + 1}}][scc][{{$package->id}}][id]" value="{{$column->id ?? 0}}">
                                        <input type="hidden" class="form-control compare_type" name="sc[{{$key + 1}}][scc][{{$package->id}}][type]" value="{{$column->type ?? 'text'}}">
                                        <input type="hidden" class="form-control boolean_value" name="sc[{{$key + 1}}][scc][{{$package->id}}][boolean_value]" value="{{$column->value ?? 1}}">
                                        <div class="d-flex align-items-center gap-4 justify-content-between">
                                            <div class="flex-1">
                                                <span class="material-symbols-outlined notranslate compare-row-icon type_boolean type_yes compare_type_value" role="button" title="<?php echo __('Yes');?>" onclick="$.rowCompare.switchYesNo($(this), 0)"> check_circle </span>
                                                <span class="material-symbols-outlined notranslate compare-row-icon type_boolean type_no compare_type_value" role="button" title="<?php echo __('No');?>" onclick="$.rowCompare.switchYesNo($(this), 1)"> cancel </span>
                                                <input type="text" class="form-control type_text compare_type_value" name="sc[{{$key + 1}}][scc][{{$package->id}}][text_value]" value="{{$column ? $column->getTranslatedAttributeValue('value', $language) : null}}" maxlength="255">
                                            </div>
                                            <span class="material-symbols-outlined notranslate compare-row-icon" role="button" onclick="$.rowCompare.switchType($(this))"> sync </span>
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                        <tr class="text-center tr-add-btn">
                            <td onclick="return $.rowCompare.add()" role="button" class="btn-full">{{__('Add new feature')}}</td>
                        </tr>
                    </tbody>
                </table>            
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-filled-blue" id="savePricingTable">{{__('Save')}}</button>
                <a class="btn-filled-blue" href="{{route('admin.user_subscription.pricing_table.preview')}}" target="_blank">{{__('Preview')}}</a>
            </div>
        </form>
    </div>
</div>
@stop

@push('scripts-body')
<script src="{{ asset('admin/js/user_subscription.js') }}"></script>
<script src="{{ asset('admin/js/lib/Sortable.min.js') }}"></script>
<script>
    adminUserSubscription.initCreatePricingTable('{{ route('admin.user_subscription.pricing_table.index')}}')
    adminUserSubscription.initComparePackages('{{route('admin.user_subscription.store_order')}}')
    adminUserSubscription.initCompareTable()
    adminTranslate.add({
        'remove_row' : '{{addslashes(__('Are you sure you want to delete this row. This cannot be undone!'))}}'
    });
</script>
@endpush