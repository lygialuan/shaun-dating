@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{ $title }}</h5>
    </div>    
    <div class="admin-card-body">
        <div class="preview-packages-list">
            @foreach ($packages as $package)
            <?php 
            $selectedPlan = $package->getPlans()[0];
            ?>
            <div class="preview-packages-list-item">
                <div class="preview-packages-list-item-header">
                    <div class="preview-packages-list-item-header-title">
                        {{$package->name}}
                    </div>
                    @if ($package->is_highlight)
                        <span class="preview-packages-list-item-header-badge" style="background-color: {{ $highlightBadge['highlight_background_color'] }}; color: {{ $highlightBadge['highlight_text_color'] }}">{{ $highlightBadgeValue }}</span>
                    @endif
                </div>
                <select class="form-select">
                    @foreach($package->getPlans() as $key=>$plan)
                        <option value="{{$key}}">{{$plan->name}}</option>
                    @endforeach
                </select>
                <div>
                    @if ($selectedPlan->trial_day)
                        <p class="preview-packages-list-item-plan-price">{{ $selectedPlan->trial_day . ' ' . __('day(s) trial then') }}</p>
                    @endif
                    <p class="preview-packages-list-item-plan-desc">{{$selectedPlan->getDescription()}}</p>
                </div>
                
                <div class="preview-packages-list-item-desc">
                    {{trim($package->getTranslatedAttributeValue('description'))}}
                </div>
                <div class="preview-packages-list-item-btn-wrap">
                    <button class="btn-filled-blue btn-size-md btn-full">
                        {{ __('Select') }}
                    </button>
                </div>
            </div>
            @endforeach
        </div>
        <div class="preview-pricing-table mb-4">
            <table>
                <thead>
                    <tr class="preview-pricing-table-header">
                        <th class="preview-pricing-table-header-col">{{ __('Features') }}</th>
                        @foreach ($packages as $package)
                            <th class="preview-pricing-table-header-col preview-pricing-table-header-col-data">
                                <div class="preview-pricing-table-header-col-title">{{$package->name}}</div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="preview-pricing-table-body">
                    @foreach ($packageCompares as $key => $packageCompare)
                        <tr class="preview-pricing-table-body-row">
                            <td class="preview-pricing-table-body-col">
                                {{$packageCompare->getTranslatedAttributeValue('name')}}
                            </td>
                            @foreach ($packages as $package)
                                <?php 
                                $column = $packageCompare->getColumn($package->id)
                                ?>
                                <td class="preview-pricing-table-body-col preview-pricing-table-body-col-data">
                                    @if($column->isText())
                                        {{$column->getTranslatedAttributeValue('value')}}
                                    @else
                                        @if ($column->value == 1)
                                            <span class="preview-pricing-table-body-col-data-check material-symbols-outlined notranslate"> check_circle </span>
                                        @else
                                            <span class="preview-pricing-table-body-col-data-uncheck material-symbols-outlined notranslate"> cancel </span>
                                        @endif
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop