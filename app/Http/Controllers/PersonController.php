<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Category;
use App\Models\Company_Category;
use App\Models\Experience;
use App\Models\Favorite;
use App\Models\Job;
use App\Models\Job_Description;
use App\Models\Job_Study_Field;
use App\Models\Job_Type;
use App\Models\Language;
use App\Models\Notification;
use App\Models\Person_Category;
use App\Models\Person_Job_Description;
use App\Models\Person_Job_Type;
use App\Models\Person_Language;
use App\Models\Person_Skill;
use App\Models\Person_Study_Field;
use App\Models\Skills;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class PersonController extends Controller
{
    public function home(Request $request)
    {
        try {
            $person = auth('person')->userOrFail();
            $jobs = Job::all();

            $recommendedJobs = [];
            foreach ($jobs as $job)
            {
                $isSelected = false;
                foreach ($person->person_job_descriptions as $job_description)
                {
                    if ($job->job_description_id == $job_description->job_description_id)
                    {
                        $recommendedJobs [] = $job->id;
                        $isSelected = true;
                        break;
                    }
                }

                if ($isSelected)
                {
                    continue;
                }


                foreach ($job->job_categories as $job_category)
                {
                    foreach ($person->person_categories as $person_category)
                    {
                        if ($job_category->category_id == $person_category->category_id)
                        {
                            $recommendedJobs [] = $job->id;
                            $isSelected = true;
                            break;
                        }
                    }

                    if ($isSelected)
                    {
                        break;
                    }

                }
            }


            $recommendedJobs = collect($recommendedJobs)->map(function($oneRecord) use ($person)
            {
                $job = Job::find($oneRecord);

                if($job->company->image)
                {
                    $job->company->image = asset('/assets/companies/' . $job->company->image );
                }

                $skillNames = [];
                foreach ($job->job_skills as $job_skill)
                {
                    $skillNames [] = $job_skill->skill->name;
                }
                $meetRequirement = false;
                if ($person->experience_year->id == 1 && $job->experience_min == 0)
                {
                    $meetRequirement = true;
                }

                if ($person->experience_year->id == 18 && $job->experience_max > 15)
                {
                    $meetRequirement = true;
                }
                if ($person->experience_year->id == 2 && $job->experience_min == 0)
                {
                    $meetRequirement = true;
                }
                $idArray = [3,4,5,6,7,8,9,10,11,12,13,14,15,16,17];

                if(in_array($person->experience_year->id,$idArray))
                {
                    $experienceArray = explode(' ',$person->experience_year->name);
                    $experienceNumber = (int)$experienceArray[0];
                    if ($experienceNumber >= $job->experience_min && $experienceNumber <= $job->experience_max)
                    {
                        $meetRequirement = true;
                    }
                }

                return
                    [
                        "id" => $job->id,
                        "image" => $job->company->image,
                        "country" => $job->country,
                        "city" => $job->city,
                        "job_type" => $job->job_type->name,
                        "company_name" => $job->company->company_name,
                        "job_description" => $job->job_description->name,
                        "skill_names" => $skillNames,
                        "meet_requirement" => $meetRequirement
                    ];
            });
            return $this->returnData(['response'], [$recommendedJobs],'Recommended Jobs Data');
        }catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

    }

    public function update(Request $request)
    {
        try {
            $person = auth('person')->userOrFail();

            if($request->email && $request->email != "")
            {
                if($person->email == $request->email)
                {
                    return $this->returnError(201, 'use the same email');
                }
                $validator = Validator::make($request->all(), [
                    'email' => 'string|email|min:5|max:255|unique:persons|unique:companies',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $person->email = $request->email;
                $person->save();
            }

            if($request->first_name && $request->first_name != "")
            {
                $validator = Validator::make($request->all(), [
                    'first_name' => 'required|string|min:3|max:255',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $person->first_name = $request->first_name;
                $person->save();
            }

            if($request->last_name && $request->last_name != "")
            {
                $validator = Validator::make($request->all(), [
                    'last_name' => 'required|string|min:3|max:255',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $person->last_name = $request->last_name;
                $person->save();
            }

            if($request->country && $request->country != "")
            {
                $validator = Validator::make($request->all(), [
                    'country' => 'required|string',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $person->country = $request->country;
                $person->save();
            }

            if($request->city && $request->city != "")
            {
                $validator = Validator::make($request->all(), [
                    'city' => 'required|string',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $person->city = $request->city;
                $person->save();
            }

            if($request->phone && $request->phone != "")
            {

                if($person->phone == $request->phone)
                {
                    return $this->returnError(201, 'use the same phone');
                }

                $validator = Validator::make($request->all(), [
                    'phone' => 'string|min:9|unique:persons|unique:companies',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $person->phone = $request->phone;
                $person->save();
            }

            if($request->nationality && $request->nationality != "")
            {
                $validator = Validator::make($request->all(), [
                    'nationality' => 'required|string',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $person->nationality = $request->nationality;
                $person->save();
            }

            if($request->carer_level_id && $request->carer_level_id != "")
            {
                $validator = Validator::make($request->all(), [
                    'carer_level_id'=>'required|exists:carer_levels,id',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $person->carer_level_id = $request->carer_level_id;
                $person->save();
            }

            if($request->experience_year_id && $request->experience_year_id != "")
            {
                $validator = Validator::make($request->all(), [
                    'experience_year_id'=>'required|exists:experience_years,id',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $person->experience_year_id = $request->experience_year_id;
                $person->save();
            }

            if($request->job_search_status_id && $request->job_search_status_id != "")
            {
                $validator = Validator::make($request->all(), [
                    'job_search_status_id'=>'required|exists:job_search_status,id',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $person->job_search_status_id = $request->job_search_status_id;
                $person->save();
            }

            if($request->min_salary && $request->min_salary != "")
            {
                $validator = Validator::make($request->all(), [
                    'min_salary' => 'required|numeric',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $person->min_salary = $request->min_salary;
                $person->save();
            }

            if($request->job_categories && $request->job_categories != "")
            {
                $validator = Validator::make($request->all(), [
                    'job_categories'=> 'required|string',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }

                $job_categoriesArray = json_decode($request->job_categories);
                if (count($job_categoriesArray) == 0)
                {
                    return $this->returnError(201, 'empty array');
                }
                foreach ($job_categoriesArray as $item) {
                    $Job_Category= Category::where('name',$item)->first();
                    if (!$Job_Category) {
                        return $this->returnError(201, 'Not job Category id');
                    }
                }

                foreach ($person->person_categories as $person_category) {
                    $person_category->delete();
                }

                foreach ($job_categoriesArray as $item)
                {
                    $Job_Category= Category::where('name',$item)->first();
                    $newPerson_Category = new Person_Category;
                    $newPerson_Category->person_id = $person->id;
                    $newPerson_Category->category_id = $Job_Category->id;
                    $newPerson_Category->save();
                }
            }

            if($request->job_descriptions && $request->job_descriptions != "")
            {
                $validator = Validator::make($request->all(), [
                    'job_descriptions'=> 'required|string',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }

                $job_descriptionsArray = json_decode($request->job_descriptions);

                if (count($job_descriptionsArray) == 0)
                {
                    return $this->returnError(201, 'empty array');
                }

                foreach ($job_descriptionsArray as $item)
                {
                    $Job_Description= Job_Description::where('name',$item)->first();
                    if (!$Job_Description)
                    {
                        return $this->returnError(201, 'Not Job Description id');
                    }
                }

                foreach ($person->person_job_descriptions as $person_job_description) {
                    $person_job_description->delete();
                }


                foreach ($job_descriptionsArray as $item)
                {
                    $Job_Description= Job_Description::where('name',$item)->first();
                    $newPersonJobDescription = new Person_Job_Description;
                    $newPersonJobDescription->person_id = $person->id;
                    $newPersonJobDescription->job_description_id = $Job_Description->id;
                    $newPersonJobDescription->save();
                }
            }

            if($request->isHideSalary == '0' || $request->isHideSalary && $request->isHideSalary != "")
            {
                $validator = Validator::make($request->all(), [
                    'isHideSalary'=> 'required|in:0,1',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $person->isHideSalary = $request->isHideSalary;
                $person->save();
            }

            if($request->job_types && $request->job_types != "")
            {
                $validator = Validator::make($request->all(), [
                    'job_types'=> 'required|string',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }



                $job_typesArray = json_decode($request->job_types);


                if (count($job_typesArray) == 0)
                {
                    return $this->returnError(201, 'empty array');
                }

                foreach ($job_typesArray as $item)
                {
                    $type = Job_Type::find($item);

                    if (!$type)
                    {
                        return $this->returnError(201, 'Not job type id');
                    }
                }


                foreach ($person->person_job_types as $person_job_type) {
                    $person_job_type->delete();
                }

                foreach ($job_typesArray as $item)
                {
                    $job_type = Job_Type::find($item);
                    $newPerson_Job_Type = new Person_Job_Type;
                    $newPerson_Job_Type->person_id = $person->id;
                    $newPerson_Job_Type->job_type_id = $job_type->id;
                    $newPerson_Job_Type->save();
                }
            }

            if($request->military_status && $request->military_status != "")
            {
                $validator = Validator::make($request->all(), [
                    'military_status' => 'required|in:"Postponed","Exempted","Completed"',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $person->military_status = $request->military_status;
                $person->save();
            }

            if($request->marital_status && $request->marital_status != "")
            {
                $validator = Validator::make($request->all(), [
                    'marital_status' => 'required|in:"Married","Single"',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $person->marital_status = $request->marital_status;
                $person->save();
            }

            if($request->derive_licence == '0' || $request->derive_licence && $request->derive_licence != "")
            {
                $validator = Validator::make($request->all(), [
                    'derive_licence' => 'required|in:0,1',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $person->derive_licence = $request->derive_licence;
                $person->save();
            }

            if($request->cv && $request->cv != "")
            {
                $validator = Validator::make($request->all(), [
                    'cv' => 'required|file',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                if($person->cv)
                {
                    $path =  public_path('/assets/cvs/'.$person->cv);
                    $image_path = $path;
                    if(File::exists($image_path)) {
                        File::delete($image_path);
                    }
                }
                $cv = $this->uploadImage($request,'cvs','cv');
                $person->cv = $cv;
                $person->save();
            }

            return  $this->returnSuccessMessage('updated Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function addEducation(Request $request)
    {
        try {
            $person = auth('person')->userOrFail();


            $validator = Validator::make($request->all(), [
                'education_level_id'=>'required|exists:education_levels,id',
                'institution_name'=> 'required|string',
                'graduation_year'=> 'required|max:'.date("2030"),
                'grade_id'=> 'required|exists:grades,id',

            ]);
            if ($validator->fails()) {
                return $this->returnValidationError(422, $validator);
            }

            $isHighSchool = false;
            if ($request->education_level_id == 4)
            {
                $validator = Validator::make($request->all(), [
                    'certification_name'=>'required|string',
                    'language_of_study'=>'required|string',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $isHighSchool = true;

            }else
            {
                $validator = Validator::make($request->all(), [
                    'job_study_field'=>'required|string',

                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
            }
            if($isHighSchool)
            {
                $newPersonStudyField = new Person_Study_Field;
                $newPersonStudyField->education_level_id = $request->education_level_id;
                $newPersonStudyField->person_id = $person->id;
                $newPersonStudyField->institution_name = $request->institution_name;
                $newPersonStudyField->graduation_year = $request->graduation_year;
                $newPersonStudyField->certification_name = $request->certification_name;
                $newPersonStudyField->language_of_study = $request->language_of_study;
                $newPersonStudyField->grade_id = $request->grade_id;
                $newPersonStudyField->save();
            }else
            {
                $job_Field = Job_Study_Field::where('name',$request->job_study_field)->first();
                $newPersonStudyField = new Person_Study_Field;
                $newPersonStudyField->education_level_id = $request->education_level_id;
                $newPersonStudyField->person_id = $person->id;
                $newPersonStudyField->job_study_field_id = $job_Field->id;
                $newPersonStudyField->institution_name = $request->institution_name;
                $newPersonStudyField->graduation_year = $request->graduation_year;
                $newPersonStudyField->grade_id = $request->grade_id;
                $newPersonStudyField->save();
            }

            return  $this->returnSuccessMessage('Added Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function deleteEducation(Request $request,$id)
    {
        try {
            $person = auth('person')->userOrFail();

            $Person_Study_Field = Person_Study_Field::find($id);
            if(!$Person_Study_Field && $Person_Study_Field->person->id != $person->id)
            {
                return $this->returnError(201, 'Not a Person Study Field');
            }
            if (count($person->person_study_fields) <= 1)
            {
                return $this->returnError(201, 'Not allow to delete');
            }
            $Person_Study_Field->delete();

            return  $this->returnSuccessMessage('Deleted Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function addSkill(Request $request)
    {
        try {
            $person = auth('person')->userOrFail();


            $validator = Validator::make($request->all(), [
                'skills'=> 'required|string',
            ]);
            if ($validator->fails()) {
                return $this->returnValidationError(422, $validator);
            }

            $skillsArray = json_decode($request->skills);


            if (count($skillsArray) == 0)
            {
                return $this->returnError(201, 'empty array');
            }

            foreach ($skillsArray as $item)
            {
                $Skill= Skills::where('name',$item)->first();
                if (!$Skill)
                {
                    return $this->returnError(201, 'Not Skill id');
                }
            }


            foreach ($person->person_skills as $person_skill)
            {
                $person_skill->delete();
            }


            foreach ($skillsArray as $item)
            {
                $Skill= Skills::where('name',$item)->first();
                $newPerson_Skill = new Person_Skill;
                $newPerson_Skill->person_id = $person->id;
                $newPerson_Skill->skill_id = $Skill->id;
                $newPerson_Skill->save();
            }

            return  $this->returnSuccessMessage('Added Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function deleteSkill(Request $request,$id)
    {
        try {
            $person = auth('person')->userOrFail();

            $Person_Skill = Person_Skill::find($id);
            if(!$Person_Skill && $Person_Skill->person->id != $person->id)
            {
                return $this->returnError(201, 'Not a Person Skill');
            }
            if (count($person->person_skills) <= 1)
            {
                return $this->returnError(201, 'Not allow to delete');
            }
            $Person_Skill->delete();

            return  $this->returnSuccessMessage('Deleted Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function addLanguage(Request $request)
    {
        try {
            $person = auth('person')->userOrFail();


            $validator = Validator::make($request->all(), [
                'person_languages'=> 'required|string',
                'proficiency'=> 'required|string',
            ]);
            if ($validator->fails()) {
                return $this->returnValidationError(422, $validator);
            }

            $person_languagesArray = json_decode($request->person_languages);

            if (count($person_languagesArray) == 0)
            {
                return $this->returnError(201, 'empty array');
            }

            foreach ($person_languagesArray as $item)
            {
                $Language= Language::find($item);
                if (!$Language)
                {
                    return $this->returnError(201, 'Not Language id');
                }
            }


            $proficiencyArray = json_decode($request->proficiency);




            if(count($person_languagesArray) != count($proficiencyArray))
            {
                return $this->returnError(201, 'languages array must be equal to proficiency array');
            }


            foreach ($person->person_languages as $person_language)
            {
                $person_language->delete();
            }

            for ($i = 0 ; $i < count($proficiencyArray) ; $i++)
            {
                $Language= Language::find($person_languagesArray[$i]);
                $newPerson_Language = new Person_Language;
                $newPerson_Language->person_id = $person->id;
                $newPerson_Language->language_id = $Language->id;
                $newPerson_Language->proficiency = $proficiencyArray[$i];
                $newPerson_Language->save();
            }
            return  $this->returnSuccessMessage('Added Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function deleteLanguage(Request $request,$id)
    {
        try {
            $person = auth('person')->userOrFail();

            $Person_Language= Person_Language::find($id);
            if(!$Person_Language && $Person_Language->person->id != $person->id)
            {
                return $this->returnError(201, 'Not a Person Language');
            }
            if (count($person->person_languages) <= 1)
            {
                return $this->returnError(201, 'Not allow to delete');
            }
            $Person_Language->delete();

            return  $this->returnSuccessMessage('Deleted Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function addExperience(Request $request)
    {
        try {
            $person = auth('person')->userOrFail();


            $validator = Validator::make($request->all(), [
                'job_title'=> 'required|string',
                'company_name'=> 'required|string',
                'category_id'=>'required|exists:categories,id',
                'job_type_id'=>'required|exists:job_types,id',
                'isCurrent'=> 'required|in:0,1',
                'start_from' => 'required|date_format:Y-m-d',
            ]);
            if ($validator->fails()) {
                return $this->returnValidationError(422, $validator);
            }

            $experience = new Experience();
            $experience->job_title = $request->job_title;
            $experience->company_name = $request->company_name;
            $experience->category_id = $request->category_id;
            $experience->job_type_id = $request->job_type_id;
            $experience->isCurrent = $request->isCurrent;
            $experience->start_from = $request->start_from;
            $experience->person_id = $person->id;

            if ($request->isCurrent == 0)
            {
                $validator = Validator::make($request->all(), [
                    'start_to' => 'required|date_format:Y-m-d',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $experience->start_to = $request->start_to;
            }
            $experience->save();
            return  $this->returnSuccessMessage('Added Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function appliedJobs(Request $request)
    {
        try {
            $person = auth('person')->userOrFail();

            $jobs = collect($person->applicants)->map(function($oneRecord) use ($person)
            {
                $job = $oneRecord->job;
                if($job->company->image)
                {
                    $job->company->image = asset('/assets/companies/' . $job->company->image );
                }
                $skillNames = [];
                foreach ($job->job_skills as $job_skill)
                {
                    $skillNames [] = $job_skill->skill->name;
                }

                $personApplicant = Applicant::where('job_id',$job->id)->where('person_id',$person->id)->first();
                if (!$personApplicant)
                {
                    $status = '0';
                }else
                {
                    $status = $personApplicant->status;
                }

                return
                    [
                        "applicant_id" => $oneRecord->id,
                        "job_id" => $job->id,
                        "image" => $job->company->image,
                        "company_name" => $job->company->company_name,
                        "job_description" => $job->job_description->name,
                        "skill_names" => $skillNames,
                        "status" => $status,
                    ];
            });
            return $this->returnData(['response'], [$jobs],'Jobs Data');
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function getApplicantsAnswerForPerson(Request $request,$id)
    {
        try {
            $person = auth('person')->userOrFail();

            $applicant = Applicant::find($id);
            if(!$applicant || $applicant->person->id != $person->id )
            {
                return $this->returnError(201, 'Not a applicant');
            }

            $answers = collect($applicant->answers)->map(function($oneRecord) use ($person)
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

    public function deleteExperience(Request $request,$id)
    {
        try {
            $person = auth('person')->userOrFail();

            $Experience = Experience::find($id);
            if(!$Experience && $Experience->person->id != $person->id)
            {
                return $this->returnError(201, 'Not a Experience');
            }
            $Experience->delete();

            return  $this->returnSuccessMessage('Deleted Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function saveJob(Request $request,$id)
    {
        try {
            $person = auth('person')->userOrFail();
            $job = Job::find($id);
            if(!$job)
            {
                return $this->returnError(201, 'invalid id !');
            }
            $favorite = Favorite::where('person_id',$person->id)
                ->where('job_id',$job->id)
                ->first();
            if($favorite)
            {
                return $this->returnError(201, 'already added !');
            }
            $newFavorite = new Favorite;
            $newFavorite->person_id = $person->id;
            $newFavorite->job_id = $job->id;
            $newFavorite->save();
            return $this->returnSuccessMessage('Added Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function unSaveJob($id)
    {
        try {
            $person = auth('person')->userOrFail();
            $job = Job::find($id);
            if(!$job)
            {
                return $this->returnError(201, 'invalid id !');
            }
            $newFavorite = Favorite::where('person_id',$person->id)->where('job_id',$job->id)->first();
            if(!$newFavorite)
            {
                return $this->returnError(201, 'Not exists !');
            }
            $newFavorite->delete();
            return $this->returnSuccessMessage('Deleted Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function getSavedJobs()
    {
        try {
            $person = auth('person')->userOrFail();
            $favorites = collect($person->favorites)->map(function($oneFavorite)
            {
                $job = $oneFavorite->job;
                if($job->company->image)
                {
                    $job->company->image = asset('/assets/companies/' . $job->company->image );
                }
                $skillNames = [];
                foreach ($job->job_skills as $job_skill)
                {
                    $skillNames [] = $job_skill->skill->name;
                }
                return
                    [
                        "job_id" => $job->id,
                        "image" => $job->company->image,
                        "company_name" => $job->company->company_name,
                        "job_description" => $job->job_description->name,
                        "skill_names" => $skillNames,
                    ];
            });
            return $this->returnData(['response'], [$favorites],'Favorite Jobs Data');
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function getPersonNotification(Request $request)
    {
        try {
            $person = auth('person')->userOrFail();
            $notifications = Notification::where('user_type','person')->where('user_id',$person->id)->where('seen',0)->orderBy('created_at', 'DESC')
                ->get();
            $counter = count($notifications);
            return $this->returnData(['response'], [$counter],'Counter Data');
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

    public function uploadImage(Request $request, $folderName,$filename)
    {

        $filename = strval($filename);
        if ($request->hasFile($filename)) {
            $extension = $request->file($filename)->extension();
            $image = time() . '.' . $request->file($filename)->getClientOriginalExtension();
            $request->file($filename)->move(public_path('/assets/'.$folderName), $image);
            return $image;

        }
    }
}
