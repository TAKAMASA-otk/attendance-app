<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class UpdateAttendanceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'start_time' => ['required'],
            'end_time' => ['required'],
            'break_start' => ['nullable', 'array'],
            'break_start.*' => ['nullable'],
            'break_end' => ['nullable', 'array'],
            'break_end.*' => ['nullable'],
            'reason' => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'reason.required' => '備考を記入してください',
            'start_time.required' => '出勤時間もしくは退勤時間が不適切な値です',
            'end_time.required' => '出勤時間もしくは退勤時間が不適切な値です',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $startTime = $this->input('start_time');
            $endTime = $this->input('end_time');

            if (!$startTime || !$endTime) {
                return;
            }

            $start = Carbon::parse($startTime);
            $end = Carbon::parse($endTime);

            if ($start->gte($end)) {
                $validator->errors()->add(
                    'start_time',
                    '出勤時間もしくは退勤時間が不適切な値です'
                );
                return;
            }

            $breakStarts = $this->input('break_start', []);
            $breakEnds = $this->input('break_end', []);

            foreach ($breakStarts as $index => $breakStart) {
                $breakEnd = $breakEnds[$index] ?? null;

                if (!$breakStart && !$breakEnd) {
                    continue;
                }

                if ($breakStart && (
                    Carbon::parse($breakStart)->lt($start) ||
                    Carbon::parse($breakStart)->gt($end)
                )) {
                    $validator->errors()->add(
                        'break_start.' . $index,
                        '休憩時間が不適切な値です'
                    );
                }

                if ($breakEnd && (
                    Carbon::parse($breakEnd)->lt($start) ||
                    Carbon::parse($breakEnd)->gt($end)
                )) {
                    $validator->errors()->add(
                        'break_end.' . $index,
                        '休憩時間もしくは退勤時間が不適切な値です'
                    );
                }

                if ($breakStart && $breakEnd && Carbon::parse($breakStart)->gte(Carbon::parse($breakEnd))) {
                    $validator->errors()->add(
                        'break_start.' . $index,
                        '休憩時間が不適切な値です'
                    );
                }
            }
        });
    }
}