<?php
namespace App\Services\Fsm;

use App\Models\BuildingInfo\Building;
use App\Models\Fsm\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PublicApplicationService
{

    private function getValidator(Request $request)
    {
        return Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'customer_contact' => 'required|string|max:20',
            'holding_owner_name' => 'nullable|string|max:255',
            'ward' => 'required|string',
            'road_code' => 'nullable|string|max:255',
            'tax_id' => 'required|string|max:50',
            'address' => 'required|string|max:500',
            'proposed_emptying_date' => 'required|date|after_or_equal:today',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ], [
            'customer_name.required' => 'Customer Name is required.',
            'customer_contact.required' => 'Contact No. is required.',
            'ward.required' => 'Ward is required.',
            'tax_id.required' => 'Tax ID is required.',
            'address.required' => 'Address is required.',
            'proposed_emptying_date.required' => 'Proposed Emptying Date is required.',
            'proposed_emptying_date.after_or_equal' => 'Proposed Emptying Date must be today or a future date.',
            'latitude.numeric' => 'Latitude must be a valid number.',
            'latitude.between' => 'Latitude must be between -90 and 90.',
            'longitude.numeric' => 'Longitude must be a valid number.',
            'longitude.between' => 'Longitude must be between -180 and 180.',
        ]);
    }

    private function getApplicationStatus(Request $request)
    {
        return Application::where('containment_id', $request->containment_id)
            ->where('emptying_status', false)
            ->whereNull('deleted_at')
            ->exists();
    }

    public function createApplication(Request $request)
    {
        dd($request->all());
        $validator = $this->getValidator($request);
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::transaction(function () use ($request) {
                $application = Application::create($request->all());

                $building = Building::where('bin', '=', $application->bin)->firstOrFail();
                $owner = $building->owners;
                $application->containment_id = $request->containment_id;
                $application->customer_name = $request->customer_name ?? $owner->owner_name;
                $application->customer_contact = $request->customer_contact ?? $owner->owner_contact;
                $application->customer_gender = $request->customer_gender ?? $owner->owner_gender;

                $owner->fill(
                    [
                        "owner_name" => $request->customer_name ?? $owner->owner_name,
                        "owner_gender" => $request->customer_gender ?? $owner->owner_gender,
                        "owner_contact" => $request->customer_contact ?? $owner->owner_contact
                    ]
                )->save();
                $building->fill([
                    "ward" => $request->ward ?? $building->ward,
                    "road_code" => $request->road_code,

                ])->save();
                $building->household_served = $request->household_served;
                $building->population_served = $request->population_served;
                $building->toilet_count = $request->toilet_count;
                $building->save();
                $application->application_date = now()->format('Y-m-d H:i:s');
                // $application->user_id = Auth::user()->id;
                if ($request->autofill === 'on') {
                    $application->applicant_name = $request->customer_name ?? $owner->owner_name ?? null;
                    $application->applicant_contact = $request->customer_contact ?? $owner->owner_contact ?? null;
                    $application->applicant_gender = $request->customer_gender ?? $owner->owner_gender ?? null;
                };
                $application->emergency_desludging_status = $request->emergency_desludging_status ?? $request->emergency_desludging_status ?? null;
                $application->save();
            });
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', "Error! Application couldn't be created.");
        }

        return redirect(route('client-fsm-application.form'))->with('success', 'Your application submitted successfully, Thank You.');
    }
}
