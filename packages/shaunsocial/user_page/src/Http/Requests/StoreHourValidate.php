<?php


namespace Packages\ShaunSocial\UserPage\Http\Requests;

use Illuminate\Validation\Rule;

class StoreHourValidate extends PageValidate
{
    public function rules()
    {
        $hours = collect(getPageInfoHourList());
        $rules = [
            'type' => [
                'required',
                Rule::in($hours->map(function ($item, int $key) {
                    return $item['value'];
                })->all())
            ],
        ];

        if ($this->input('type') == 'hours') {
            $rules['values'] = [
                'required',
                'string',
                function ($attribute, $values, $fail) {
                    if (! validateJson($values)) {
                        return $fail(__('The values must be json format.'));
                    }

                    $hours = json_decode($values, true);
                    $dayOfWeeks = array_keys(getPageInfoDayOfWeekList());
                    foreach ($hours as $day => $hour) {
                        if (! in_array($day, $dayOfWeeks)) {
                            return $fail(__('The day of week must be correct format.'));
                        }

                        if (empty($hour['start']) || empty($hour['end'])) {
                            return $fail(__('The start, end must be correct format.'));
                        }

                        if (! $this->checkTime($hour['start']) || ! $this->checkTime($hour['end'])) {
                            return $fail(__('The start, end must be correct format.'));
                        }
                        $start = str_replace(':', '', $hour['start']);
                        $end = str_replace(':', '', $hour['end']);
                        if ($start > $end) {
                            return $fail(__('The end must be greater than the start.'));
                        }
                    }
                },
            ];
        }

        return $rules;
    }

    private function checkTime($time)
    {
        $times = explode(':', $time);
        if (count($times) != 2) {
            return false;
        }
        if (strlen($times[0]) != 2 || strlen($times[0]) != 2) {
            return false;
        }
        if (! is_numeric($times[0]) || ! is_numeric($times[1])) {
            return false;
        }
        if (intval($times[0]) < 0 || intval($times[0]) > 23) {
            return false;
        }
        if (intval($times[1]) < 0 || intval($times[1]) > 59) {
            return false;
        }
        return true;
    }

    public function messages()
    {
        return [
            'type.required' => __('The type is required.'),
            'type.in' => __('The type is not in the list.'),
            'values.required' => __('The values is required.')
        ];
    }
}
