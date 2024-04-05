<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;

class LeadController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadLeads(Request $request){

        $validator = Validator::make($request->all(),[
            'file' => 'required|mimes:xls,xlsx,csv|max:2048',
        ]);

        if ($validator->fails()){
            return response()->json(['errors' => $validator->errors()]);
        }

        $file = $request->file('file');
        $spreadsheet = IOFactory::load($file);
        $worksheet = $spreadsheet->getActiveSheet();


        foreach ($worksheet->getRowIterator(2) as $row){
            $rowData = [];
            foreach ($row->getCellIterator() as $cell){
                $rowData[] = (string)$cell->getValue();
            }

            Lead::firstOrCreate([
                'name' => $rowData[0],
                'phone_number' => $rowData[1],
                'account_number' => $rowData[2],
                'ippis_number' => $rowData[3],
                'organization_name' => $rowData[4],
                'state_name' => $rowData[5],
                'city_name' => $rowData[6],
            ]);

//            $lead = new Lead();
//            $lead->name = $rowData[0];
//            $lead->phone_number = $rowData[1];
//            $lead->account_number = $rowData[2];
//            $lead->ippis_number = $rowData[3];
//            $lead->organization_name = $rowData[4];
//            $lead->state_name = $rowData[5];
//            $lead->city_name = $rowData[6];
//            $lead->save();


        }
        return response()->json(['message' => 'Excel data imported successfully.'],201);

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reassignLead(Request $request){
        $validator = Validator::make($request->all(),[
           'user_id' => 'required|integer',
            'lead_ids' => 'required|array',
            'lead_ids.*' => 'integer|exists:leads,id'
        ]);
//        if (!auth()->user()->can('reassign-leads')){
//            abort(403, 'Unauthorized');
//        }
//        $user = auth()->user()->can('reassign-leads') ? auth()->user() : null;
//        if (!$user) {
//            return abort(403, 'Unauthorized');
//        }
        if ($validator->fails()){
            return response()->json(['errors' => $validator->errors()]);
        }
        $leads = Lead::whereIn('id', $request->lead_ids)->get();
//        $leads->each(function ($lead) use ($user) {
//            $lead->user_id = $user->id;
//            $leads->save();
//        });
        foreach ($leads as $lead){
            $lead->user_id = $request->user_id;
            $lead->save();
        }



        return response()->json([
            'data' => $leads,
            'message' => 'Leads reassigned successfully'
        ],201);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteLeads(Request $request){

        $validator = Validator::make($request->all(),[
            'lead_ids' => 'required|array',
            'lead_ids.*' => 'integer|exists:leads,id'
        ]);
        if ($validator->fails()){
            return response()->json(['errors' => $validator->errors()]);
        }
//        $user = auth()->user()->can('delete-leads') ? auth()->user() : null;
//        if (!$user) {
//            return abort(403, 'Unauthorized');
//        }
        $leads = Lead::whereIn('id',$request->lead_ids)->get();
        $leadsCount = Lead::whereIn('id',$request->lead_ids)->delete();

        return response()->json([
            'leads_deleted' => $leads,
            'leads_count' => $leadsCount,
            'message' => 'Leads deleted successfully'
        ],200);
    }

    /**
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function viewLeadsByAccountOfficer(User $user){

//        $user = auth()->user()->can('view-all-leads') ? auth()->user() : null;
//        if (!$user){
//            return abort(403, 'Unauthorized');
//        }
//        $leads = Lead::where('user_id', $user->id)->get();
        return response()->json($user->leads()->get(), 200);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function viewAllLeads(){
//        $user = auth()->user() ? auth()->user() : null;
//        if (!$user){
//            return redirect()->route('login');
//        }
        $leads = Lead::paginate(10);
        return response()->json($leads->toArray(), 200);
    }
}
