<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Category;
use App\Models\Company_Category;
use App\Models\Job;
use App\Models\Notification;
use App\Models\Tournament;
use App\Models\Tournament_Competitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
    public function home(Request $request)
    {
        try {
            $company = auth('company')->userOrFail();

            $jobs = [];
            foreach ($company->jobs as $job)
            {
                $skillNames = [];
                foreach ($job->job_skills as $job_skill)
                {
                    $skillNames [] = $job_skill->skill->name;
                }

                $categoryNames = [];
                foreach ($job->job_categories as $job_category)
                {
                    $categoryNames [] = $job_category->category->name;
                }


                $object = [
                    'id' => $job->id,
                    'job_description' => $job->job_description->name,
                    'carer_level' => $job->carer_level->name,
                    'experience_min' => $job->experience_min,
                    'experience_max' => $job->experience_max,
                    'country' => $job->country,
                    'applicants' => count($job->applicants),
                    'city' => $job->city,
                    'skill_names' => $skillNames,
                    'category_names' => $categoryNames,
                    'date' => $job->created_at->format('d-m-Y'),
                ];
                $jobs [] = $object;
            }

            return $this->returnData(['response'], [$jobs],'Jobs Data');
        }catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

    }

    public function update(Request $request)
    {
        try {
            $company = auth('company')->userOrFail();

            if($request->email && $request->email != "")
            {
                if($company->email == $request->email)
                {
                    return $this->returnError(201, 'use the same email');
                }
                $validator = Validator::make($request->all(), [
                    'email' => 'string|email|min:5|max:255|unique:persons|unique:companies',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $company->email = $request->email;
                $company->save();
            }

            if($request->first_name && $request->first_name != "")
            {
                $validator = Validator::make($request->all(), [
                    'first_name' => 'required|string|min:3|max:255',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $company->first_name = $request->first_name;
                $company->save();
            }

            if($request->last_name && $request->last_name != "")
            {
                $validator = Validator::make($request->all(), [
                    'last_name' => 'required|string|min:3|max:255',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $company->last_name = $request->last_name;
                $company->save();
            }

            if($request->country && $request->country != "")
            {
                $validator = Validator::make($request->all(), [
                    'country' => 'required|string',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $company->country = $request->country;
                $company->save();
            }

            if($request->city && $request->city != "")
            {
                $validator = Validator::make($request->all(), [
                    'city' => 'required|string',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $company->city = $request->city;
                $company->save();
            }

            if($request->phone && $request->phone != "")
            {

                if($company->phone == $request->phone)
                {
                    return $this->returnError(201, 'use the same phone');
                }

                $validator = Validator::make($request->all(), [
                    'phone' => 'string|min:9|unique:persons|unique:companies',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $company->phone = $request->phone;
                $company->save();
            }

            if($request->title && $request->title != "")
            {
                $validator = Validator::make($request->all(), [
                    'title' => 'required|string',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $company->title = $request->title;
                $company->save();
            }

            if($request->company_name && $request->company_name != "")
            {
                $validator = Validator::make($request->all(), [
                    'company_name' => 'required|string',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $company->company_name = $request->company_name;
                $company->save();
            }

            if($request->company_categories && $request->company_categories != "")
            {
                $validator = Validator::make($request->all(), [
                    'company_categories' => 'required|string',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }

                $company_categoriesArray = json_decode($request->company_categories);
                if (count($company_categoriesArray) == 0)
                {
                    return $this->returnError(201, 'empty array');
                }
                foreach ($company_categoriesArray as $item) {
                    $type = Category::find($item);
                    if (!$type) {
                        return $this->returnError(201, 'Not job Category id');
                    }
                }

                foreach ($company->company_categories as $company_category) {
                    $company_category->delete();
                }


                foreach ($company_categoriesArray as $item) {
                    $type = Category::find($item);
                    $newCompany_Category = new Company_Category;
                    $newCompany_Category->company_id = $company->id;
                    $newCompany_Category->category_id = $type->id;
                    $newCompany_Category->save();
                }
            }

            if($request->company_size && $request->company_size != "")
            {
                $validator = Validator::make($request->all(), [
                    'company_size' => 'required|string',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $company->company_size = $request->company_size;
                $company->save();
            }

            if($request->description && $request->description != "")
            {
                $validator = Validator::make($request->all(), [
                    'description' => 'required|string|min:3|max:255',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $company->description = $request->description;
                $company->save();
            }

            if($request->website && $request->website != "")
            {
                $validator = Validator::make($request->all(), [
                    'website' => 'required|string|min:3|max:255',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $company->website = $request->website;
                $company->save();
            }

            return $this->returnSuccessMessage('Updated Successfully',200);
        }catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function getApplicants(Request $request,$id)
    {
        try {
            $company = auth('company')->userOrFail();

            $job = Job::find($id);
            if(!$job || $job->company->id != $company->id)
            {
                return $this->returnError(201, 'Not a job');
            }


            $tornament = Tournament::where('job_description_id',$job->job_description_id)
                ->where('country',$job->country)
                ->where('city',$job->city)
                ->first();


            $applicants = collect($job->applicants)->map(function($oneRecord) use ($company,$job,$tornament)
            {
                if($oneRecord->person->image)
                {
                    $oneRecord->person->image = asset('/assets/persons/' . $oneRecord->person->image );
                }
                $jobDescriptionNames = [];
                foreach ($oneRecord->person->person_job_descriptions as $job_description)
                {
                    $jobDescriptionNames [] = $job_description->job_description->name;
                }
                $personSkillsNames = [];
                foreach ($oneRecord->person->person_skills as $person_skill)
                {

                    $personSkillsNames [] = $person_skill->skill->name;
                }

                $personApplicant = Applicant::where('job_id',$job->id)->where('person_id',$oneRecord->person->id)->first();
                if (!$personApplicant)
                {
                    $status = '0';
                }else
                {
                    $status = $personApplicant->status;
                }

                if ($tornament)
                {

                    $compatators = Tournament_Competitor::where('tournament_id',$tornament->id)->orderBy('points', 'DESC')->orderBy('created_at', 'ASC')->get();

                    $rank = 0;

                    for ($i = 0 ; $i < count($compatators) ; $i++)
                    {
                        if ($compatators[$i]->person->id == $oneRecord->person->id)
                        {
                            $rank = $i + 1;
                        }
                    }
                }else
                {
                    $rank = 0;
                }




                return
                    [
                        "applicant_id" => $oneRecord->id,
                        "job_id" => $job->id,
                        "person_id" => $oneRecord->person->id,
                        "first_name" => $oneRecord->person->first_name,
                        "last_name" => $oneRecord->person->last_name,
                        "image" => $oneRecord->person->image,
                        "date" => $oneRecord->date,
                        "job_description" => $oneRecord->job->job_description->name,
                        "person_job_description" => $jobDescriptionNames,
                        'experience_years' => $oneRecord->person->experience_year->name,
                        'carer_level' =>$oneRecord->person->carer_level->name,
                        'personSkillsNames' =>$personSkillsNames,
                        'institution_name' =>$oneRecord->person->person_study_fields[0]->institution_name,
                        'status' =>$status,
                        'rank' =>$rank,
                    ];
            });
            return $this->returnData(['response'], [$applicants],'Applicants Data');
        }catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function getAccepts(Request $request,$id)
    {
        try {
            $company = auth('company')->userOrFail();

            $job = Job::find($id);
            if(!$job || $job->company->id != $company->id)
            {
                return $this->returnError(201, 'Not a job');
            }

            $applicants = Applicant::where('job_id',$job->id)->where('status','2')->get();

            $applicants = collect($applicants)->map(function($oneRecord) use ($company,$job)
            {
                if($oneRecord->person->image)
                {
                    $oneRecord->person->image = asset('/assets/persons/' . $oneRecord->person->image );
                }
                $jobDescriptionNames = [];
                foreach ($oneRecord->person->person_job_descriptions as $job_description)
                {
                    $jobDescriptionNames [] = $job_description->job_description->name;
                }
                $personSkillsNames = [];
                foreach ($oneRecord->person->person_skills as $person_skill)
                {
                    $personSkillsNames [] = $person_skill->skill->name;
                }

                $personApplicant = Applicant::where('job_id',$job->id)->where('person_id',$oneRecord->person->id)->first();
                if (!$personApplicant)
                {
                    $status = '0';
                }else
                {
                    if ($personApplicant->status == '1')
                    {
                        $status = '1';
                    }elseif ($personApplicant->status == '2')
                    {
                        $status = '2';
                    }elseif ($personApplicant->status == '3')
                    {
                        $status = '3';
                    }
                }

                return
                    [
                        "applicant_id" => $oneRecord->id,
                        "job_id" => $job->id,
                        "person_id" => $oneRecord->person->id,
                        "first_name" => $oneRecord->person->first_name,
                        "last_name" => $oneRecord->person->last_name,
                        "image" => $oneRecord->person->image,
                        "date" => $oneRecord->date,
                        "job_description" => $oneRecord->job->job_description->name,
                        "person_job_description" => $jobDescriptionNames,
                        'experience_years' => $oneRecord->person->experience_year->name,
                        'carer_level' =>$oneRecord->person->carer_level->name,
                        'personSkillsNames' =>$personSkillsNames,
                        'institution_name' =>$oneRecord->person->person_study_fields[0]->institution_name,
                        'status' =>$status,
                    ];
            });
            return $this->returnData(['response'], [$applicants],'Applicants Data');
        }catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function getDeclines(Request $request,$id)
    {
        try {
            $company = auth('company')->userOrFail();

            $job = Job::find($id);
            if(!$job || $job->company->id != $company->id)
            {
                return $this->returnError(201, 'Not a job');
            }

            $applicants = Applicant::where('job_id',$job->id)->where('status','3')->get();

            $applicants = collect($applicants)->map(function($oneRecord) use ($company,$job)
            {
                if($oneRecord->person->image)
                {
                    $oneRecord->person->image = asset('/assets/persons/' . $oneRecord->person->image );
                }
                $jobDescriptionNames = [];
                foreach ($oneRecord->person->person_job_descriptions as $job_description)
                {
                    $jobDescriptionNames [] = $job_description->job_description->name;
                }
                $personSkillsNames = [];
                foreach ($oneRecord->person->person_skills as $person_skill)
                {
                    $personSkillsNames [] = $person_skill->skill->name;
                }

                $personApplicant = Applicant::where('job_id',$job->id)->where('person_id',$oneRecord->person->id)->first();
                if (!$personApplicant)
                {
                    $status = '0';
                }else
                {
                    if ($personApplicant->status == '1')
                    {
                        $status = '1';
                    }elseif ($personApplicant->status == '2')
                    {
                        $status = '2';
                    }elseif ($personApplicant->status == '3')
                    {
                        $status = '3';
                    }
                }

                return
                    [
                        "applicant_id" => $oneRecord->id,
                        "job_id" => $job->id,
                        "person_id" => $oneRecord->person->id,
                        "first_name" => $oneRecord->person->first_name,
                        "last_name" => $oneRecord->person->last_name,
                        "image" => $oneRecord->person->image,
                        "date" => $oneRecord->date,
                        "job_description" => $oneRecord->job->job_description->name,
                        "person_job_description" => $jobDescriptionNames,
                        'experience_years' => $oneRecord->person->experience_year->name,
                        'carer_level' =>$oneRecord->person->carer_level->name,
                        'personSkillsNames' =>$personSkillsNames,
                        'institution_name' =>$oneRecord->person->person_study_fields[0]->institution_name,
                        'status' =>$status,
                    ];
            });
            return $this->returnData(['response'], [$applicants],'Applicants Data');
        }catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function getApplicantsAnswer(Request $request,$id)
    {
        try {
            $company = auth('company')->userOrFail();

            $applicant = Applicant::find($id);
            if(!$applicant )
            {
                return $this->returnError(201, 'Not a applicant');
            }

            $answers = collect($applicant->answers)->map(function($oneRecord) use ($company)
            {
                if ($oneRecord->job_form->type == 'CV')
                {
                    $oneRecord->answer = asset('/assets/appliedCVS/' . $oneRecord->answer);
                }elseif ($oneRecord->job_form->type == 'record')
                {
                    $oneRecord->answer = asset('/assets/records/' . $oneRecord->answer);
                }
                return
                    [
                        "id" => $oneRecord->id,
                        "answer" => $oneRecord->answer,
                        "question_title" => $oneRecord->job_form->question_title,
                        "type" => $oneRecord->job_form->type,
                    ];
            });
            return $this->returnData(['response'], [$answers],'Answers Data');
        }catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function getCompanyNotification(Request $request)
    {
        try {
            $company = auth('company')->userOrFail();
            $notifications = Notification::where('user_type','company')->where('user_id',$company->id)->where('seen',0)->orderBy('created_at', 'DESC')
                ->get();
            $counter = count($notifications);
            return $this->returnData(['response'], [$counter],'Counter Data');
        }catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function dashboard(Request $request)
    {
        try {
            $company = auth('company')->userOrFail();
            $jobsCounter = count($company->jobs);
            $applicantCounter = 0;
            $applicantAcceptCounter = 0;
            foreach ($company->jobs as $job)
            {
                $applicantCounter += count($job->applicants);
                foreach ($job->applicants as $applicant)
                {
                    if ($applicant->status == '2')
                    {
                        $applicantAcceptCounter++;
                    }
                }
            }
            $result =
                [
                  'jobsCounter' => $jobsCounter,
                  'applicantCounter' => $applicantCounter,
                  'applicantAcceptCounter' => $applicantAcceptCounter,
                  'company_size' => $company->company_size,
                ];
            return $this->returnData(['response'], [$result],'Dashboard Data');
        }catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function getShortList($job_id)
    {
        try {
            $company = auth('company')->userOrFail();

            $job = Job::find($job_id);


            if(!$job || $job->company->id != $company->id)
            {
                return $this->returnError(201, 'Not a job');
            }


            $short_lists = collect($job->short_lists)->map(function($oneRecord) use ($company,$job)
            {
                $applicant = $oneRecord->applicant;
                if($applicant->person->image)
                {
                    $applicant->person->image = asset('/assets/persons/' . $applicant->person->image );
                }
                $jobDescriptionNames = [];
                foreach ($applicant->person->person_job_descriptions as $job_description)
                {
                    $jobDescriptionNames [] = $job_description->job_description->name;
                }
                $personSkillsNames = [];
                foreach ($applicant->person->person_skills as $person_skill)
                {

                    $personSkillsNames [] = $person_skill->skill->name;
                }

                $personApplicant = Applicant::where('job_id',$job->id)->where('person_id',$applicant->person->id)->first();
                if (!$personApplicant)
                {
                    $status = '0';
                }else
                {
                    $status = $personApplicant->status;
                }
                return
                    [
                        "applicant_id" => $applicant->id,
                        "job_id" => $job->id,
                        "person_id" => $applicant->person->id,
                        "first_name" => $applicant->person->first_name,
                        "last_name" => $applicant->person->last_name,
                        "image" => $applicant->person->image,
                        "date" => $applicant->date,
                        "job_description" => $applicant->job->job_description->name,
                        "person_job_description" => $jobDescriptionNames,
                        'experience_years' => $applicant->person->experience_year->name,
                        'carer_level' =>$applicant->person->carer_level->name,
                        'personSkillsNames' =>$personSkillsNames,
                        'institution_name' =>$applicant->person->person_study_fields[0]->institution_name,
                        'status' =>$status,
                    ];
            });
            return $this->returnData(['response'], [$short_lists],'Short List Data');
        }catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function returnValidationError($code , $validator)
    {
        return $this->returnError($code, $validator->errors()->first());
    }

    public function returnError($errNum, $msg)
    {
        return response([
            'status' => false,
            'code' => $errNum,
            'msg' => $msg
        ], $errNum)
            ->header('Content-Type', 'text/json');
    }

    public function returnSuccessMessage($msg = '', $errNum = 'S000')
    {
        return [
            'status' => true,
            'msg' => $msg
        ];
    }

    public function returnData($keys, $values, $msg = '')
    {
        $data = [];
        for ($i = 0; $i < count($keys); $i++) {
            $data[$keys[$i]] = $values[$i];
        }

        return response()->json([
            'status' => true,
            'msg' => $msg,
            'data' => $data
        ]);
    }
}
