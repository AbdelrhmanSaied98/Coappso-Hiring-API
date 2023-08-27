<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Category;
use App\Models\Company;
use App\Models\Company_Category;
use App\Models\Favorite;
use App\Models\Job;
use App\Models\Job_Description;
use App\Models\Job_Type;
use App\Models\Person;
use App\Models\Person_Category;
use App\Models\Person_Job_Description;
use App\Models\Person_Job_Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function getPersons($numOfPage,$numOfRows)
    {
        try {
            $admin = auth('admin')->userOrFail();

            $persons = Person::all();
            $counter = count($persons);
            $skippedNumbers = ($numOfPage - 1) * $numOfRows;

            $persons = Person::skip($skippedNumbers)
                ->take($numOfRows)
                ->get();
            $persons = collect($persons)->map(function($oneRecord)
            {
                if($oneRecord->image)
                {
                    $oneRecord->image = asset('/assets/persons/' . $oneRecord->image);
                }

                $jobDescriptionNames = [];
                foreach ($oneRecord->person_job_descriptions as $job_description)
                {
                    $jobDescriptionNames [] = $job_description->job_description->name;
                }

                $personSkillsNames = [];
                foreach ($oneRecord->person_skills as $person_skill)
                {

                    $personSkillsNames [] = $person_skill->skill->name;
                }

                return
                    [
                        "id" => $oneRecord->id,
                        "first_name" => $oneRecord->first_name,
                        "last_name" => $oneRecord->last_name,
                        "ban_times" => $oneRecord->ban_times,
                        "isBlocked" => $oneRecord->isBlocked,
                        "image" => $oneRecord->image,
                        "person_job_description" => $jobDescriptionNames,
                        'experience_years' => $oneRecord->experience_year->name,
                        'carer_level' =>$oneRecord->carer_level->name,
                        'personSkillsNames' =>$personSkillsNames,
                        'institution_name' => $oneRecord->person_study_fields[0]->institution_name,
                    ];
            });
            $result = [
                'counter'=>$counter,
                'persons'=>$persons
            ];
            return $this->returnData(['response'], [$result],'Persons Data');
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function getPersonsSearch($name,$numOfPage,$numOfRows)
    {
        try {
            $admin = auth('admin')->userOrFail();

            $persons = Person::where(function($query) use ($name) {
                $query->where('first_name', 'LIKE',$name.'%')
                    ->orWhere('last_name', $name.'%');
            })->get();
            $counter = count($persons);
            $skippedNumbers = ($numOfPage - 1) * $numOfRows;

            $persons = Person::where(function($query) use ($name) {
                $query->where('first_name', 'LIKE',$name.'%')
                    ->orWhere('last_name', $name.'%');
            })->skip($skippedNumbers)
                ->take($numOfRows)
                ->get();
            $persons = collect($persons)->map(function($oneRecord)
            {
                if($oneRecord->image)
                {
                    $oneRecord->image = asset('/assets/persons/' . $oneRecord->image);
                }

                $jobDescriptionNames = [];
                foreach ($oneRecord->person_job_descriptions as $job_description)
                {
                    $jobDescriptionNames [] = $job_description->job_description->name;
                }

                $personSkillsNames = [];
                foreach ($oneRecord->person_skills as $person_skill)
                {

                    $personSkillsNames [] = $person_skill->skill->name;
                }

                return
                    [
                        "id" => $oneRecord->id,
                        "first_name" => $oneRecord->first_name,
                        "last_name" => $oneRecord->last_name,
                        "image" => $oneRecord->image,
                        "ban_times" => $oneRecord->ban_times,
                        "isBlocked" => $oneRecord->isBlocked,
                        "person_job_description" => $jobDescriptionNames,
                        'experience_years' => $oneRecord->experience_year->name,
                        'carer_level' =>$oneRecord->carer_level->name,
                        'personSkillsNames' =>$personSkillsNames,
                        'institution_name' => $oneRecord->person_study_fields[0]->institution_name,
                    ];
            });
            $result = [
                'counter'=>$counter,
                'persons'=>$persons
            ];
            return $this->returnData(['response'], [$result],'Persons Data');
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function getPersonDetails($id)
    {
        try {
            $admin = auth('admin')->userOrFail();

            $person = Person::find($id);

            if(! $person )
            {
                return $this->returnError(201, 'Invalid id');
            }

            if($person->image)
            {
                $person->image = asset('/assets/persons/' . $person->image );
            }

            $jobDescriptionNames = [];
            foreach ($person->person_job_descriptions as $job_description)
            {
                $jobDescriptionNames [] = $job_description->job_description->name;
            }

            $jobCategoriesNames = [];
            foreach ($person->person_categories as $person_category)
            {
                $jobCategoriesNames [] = $person_category->category->name;
            }


            $jobTypeNames = [];
            $jobTypeIDs = [];
            foreach ($person->person_job_types as $person_job_type)
            {
                $jobTypeNames [] = $person_job_type->job_type->name;
                $jobTypeIDs [] = $person_job_type->job_type->id;
            }


            $dateOfBirth = $person->birth_date;
            $today = date("Y-m-d");
            $diff = date_diff(date_create($dateOfBirth), date_create($today));



            $workExperiences = [];

            foreach ($person->experiences as $experience)
            {
                if($experience->isCurrent == 0)
                {
                    $object =
                        [
                            'id' => $experience->id,
                            'company_name' => $experience->company_name,
                            'job_title' => $experience->job_title,
                            'category_name' => $experience->category->name,
                            'job_type' => $experience->job_type->name,
                            'start_from' => $experience->start_from,
                            'start_to' => $experience->start_to,
                        ];
                }else
                {
                    $object =
                        [
                            'id' => $experience->id,
                            'company_name' => $experience->company_name,
                            'job_title' => $experience->job_title,
                            'category_name' => $experience->category->name,
                            'job_type' => $experience->job_type->name,
                            'start_from' => $experience->start_from,
                            'start_to' => "Current",
                        ];
                }

                $workExperiences [] = $object;
            }



            $education = [];
            foreach ($person->person_study_fields as $person_study_field)
            {
                if($person_study_field->education_level_id == 4)
                {
                    $object =
                        [
                            'id' => $person_study_field->id,
                            'institution_name' => $person_study_field->institution_name,
                            'graduation_year' => $person_study_field->graduation_year,
                            'grade' => $person_study_field->grade->name,
                            'certification_name' => $person_study_field->certification_name,
                            'language_of_study' => $person_study_field->language_of_study,
                            'education_level' => $person_study_field->education_level->name,
                        ];
                }else
                {
                    $object =
                        [
                            'id' => $person_study_field->id,
                            'institution_name' => $person_study_field->institution_name,
                            'graduation_year' => $person_study_field->graduation_year,
                            'grade' => $person_study_field->grade->name,
                            'job_study_field' => $person_study_field->job_study_field->name,
                            'education_level' => $person_study_field->education_level->name,
                        ];
                }

                $education [] = $object;
            }


            $personSkillsNames = [];
            foreach ($person->person_skills as $person_skill)
            {
                $object =
                    [
                        'id' => $person_skill->skill->id,
                        'name' => $person_skill->skill->name,
                    ];
                $personSkillsNames [] = $object;
            }

            $personLanguageNames = [];
            foreach ($person->person_languages as $person_language)
            {
                $object =
                    [
                        'id' => $person_language->language->id,
                        'name' => $person_language->language->name,
                        'proficiency' => $person_language->proficiency,
                    ];
                $personLanguageNames [] = $object;
            }

            $min_salary = $person->min_salary;

            if($person->cv)
            {
                $person->cv = asset('/assets/cvs/' . $person->cv );
            }


            $result =
                [
                    'id' => $person->id,
                    'first_name' => $person->first_name,
                    'last_name' => $person->last_name,
                    'image' => $person->image,
                    'job_description_names' => $jobDescriptionNames,
                    'age' => $diff->format('%y'),
                    'experience_years' => $person->experience_year->name,
                    'experience_year_id' => $person->experience_year->id,
                    'job_search_status' => $person->job_search_status->name,
                    'job_search_status_id' => $person->job_search_status->id,
                    'email' => $person->email,
                    'phone' =>$person->phone,
                    'country' =>$person->country,
                    'city' =>$person->city,
                    'isHideSalary' =>$person->isHideSalary,
                    'nationality' =>$person->nationality,
                    'gender' =>$person->gender,
                    'military_status' =>$person->military_status,
                    'marital_status' =>$person->marital_status,
                    'derive_licence' =>$person->derive_licence,
                    'cv' =>$person->cv,
                    'carer_level' =>$person->carer_level->name,
                    'carer_level_id' =>$person->carer_level->id,
                    'min_salary' => $min_salary,
                    'job_category_names' => $jobCategoriesNames,
                    'job_type_names' => $jobTypeNames,
                    'job_type_ids' => $jobTypeIDs,
                    'workExperiences' => $workExperiences,
                    "education" => $education,
                    "person_skills_names" => $personSkillsNames,
                    "personLanguageNames" => $personLanguageNames,
                ];
            return $this->returnData(['response'], [$result],'Person Data');

        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function getCompanies($numOfPage,$numOfRows)
    {
        try {
            $admin = auth('admin')->userOrFail();

            $companies = Company::all();
            $counter = count($companies);
            $skippedNumbers = ($numOfPage - 1) * $numOfRows;

            $companies = Company::skip($skippedNumbers)
                ->take($numOfRows)
                ->get();
            $companies = collect($companies)->map(function($oneRecord)
            {
                if($oneRecord->image)
                {
                    $oneRecord->image = asset('/assets/companies/' . $oneRecord->image);
                }

                return
                    [
                        "id" => $oneRecord->id,
                        "company_name" => $oneRecord->company_name,
                        "country" => $oneRecord->country,
                        "image" => $oneRecord->image,
                        "ban_times" => $oneRecord->ban_times,
                        "isBlocked" => $oneRecord->isBlocked,
                        'city' => $oneRecord->city,
                    ];
            });
            $result = [
                'counter'=>$counter,
                'companies'=>$companies
            ];
            return $this->returnData(['response'], [$result],'Companies Data');
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function getCompaniesSearch($name,$numOfPage,$numOfRows)
    {
        try {
            $admin = auth('admin')->userOrFail();

            $companies = Company::where(function($query) use ($name) {
                $query->where('company_name', 'LIKE',$name.'%');
            })->get();
            $counter = count($companies);
            $skippedNumbers = ($numOfPage - 1) * $numOfRows;

            $companies = Company::where(function($query) use ($name) {
                $query->where('company_name', 'LIKE',$name.'%');
            })->skip($skippedNumbers)
                ->take($numOfRows)
                ->get();
            $companies = collect($companies)->map(function($oneRecord)
            {
                if($oneRecord->image)
                {
                    $oneRecord->image = asset('/assets/companies/' . $oneRecord->image);
                }

                return
                    [
                        "id" => $oneRecord->id,
                        "company_name" => $oneRecord->company_name,
                        "country" => $oneRecord->country,
                        "image" => $oneRecord->image,
                        "ban_times" => $oneRecord->ban_times,
                        "isBlocked" => $oneRecord->isBlocked,
                        'city' => $oneRecord->city,
                    ];
            });
            $result = [
                'counter'=>$counter,
                'companies'=>$companies
            ];
            return $this->returnData(['response'], [$result],'Companies Data');
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function getCompanyDetails($id)
    {
        try {
            $admin = auth('admin')->userOrFail();

            $company = Company::find($id);

            if(! $company )
            {
                return $this->returnError(201, 'Invalid id');
            }

            if($company->image)
            {
                $company->image = asset('/assets/companies/' . $company->image );
            }


            $companyCategoriesNames = [];
            $companyCategoriesIDs = [];
            foreach ($company->company_categories as $company_category)
            {
                $companyCategoriesNames [] = $company_category->category->name;
                $companyCategoriesIDs [] = $company_category->category->id;
            }

            $jobs = [];
            foreach ($company->jobs as $job)
            {
                $skillNames = [];
                foreach ($job->job_skills as $job_skill)
                {
                    $skillNames [] = $job_skill->skill->name;
                }
                $object = [
                    'id' => $job->id,
                    'job_description' => $job->job_description->name,
                    'carer_level' => $job->carer_level->name,
                    'experience_min' => $job->experience_min,
                    'experience_max' => $job->experience_max,
                    'country' => $job->country,
                    'city' => $job->city,
                    'skill_names' => $skillNames,
                    'date' => $job->created_at->format('d-m-Y'),
                ];
                $jobs [] = $object;
            }


            $result =
                [
                    'id' => $company->id,
                    'name' => $company->company_name,
                    'first_name' => $company->first_name,
                    'last_name' => $company->last_name,
                    'image' => $company->image,
                    'email' => $company->email,
                    'phone' => $company->phone,
                    'country' => $company->country,
                    'city' => $company->city,
                    'description' => $company->description,
                    'website' => $company->website,
                    'company_size' => $company->company_size,
                    'title' => $company->title,
                    'companyCategoriesNames' => $companyCategoriesNames,
                    'companyCategoriesIDs' => $companyCategoriesIDs,
                    'jobs' => $jobs,
                ];
            return $this->returnData(['response'], [$result],'Company Data');

        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function getJobs($numOfPage,$numOfRows)
    {
        try {
            $admin = auth('admin')->userOrFail();

            $jobs = Job::all();
            $counter = count($jobs);
            $skippedNumbers = ($numOfPage - 1) * $numOfRows;

            $jobs = Job::skip($skippedNumbers)
                ->take($numOfRows)
                ->get();
            $jobs = collect($jobs)->map(function($job)
            {
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
                        "id" => $job->id,
                        "image" => $job->company->image,
                        "country" => $job->country,
                        "city" => $job->city,
                        "job_type" => $job->job_type->name,
                        "company_name" => $job->company->company_name,
                        "job_description" => $job->job_description->name,
                        "skill_names" => $skillNames,
                    ];
            });
            $result = [
                'counter'=>$counter,
                'jobs'=>$jobs
            ];
            return $this->returnData(['response'], [$result],'Jobs Data');
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function getJobDetails($id)
    {
        try {
            $admin = auth('admin')->userOrFail();

            $job = Job::find($id);
            if(!$job)
            {
                return $this->returnError(201, 'Not a job');
            }

            if($job->company->image)
            {
                $job->company->image = asset('/assets/companies/' . $job->company->image );
            }

            $min_salary = $job->salary_min;
            $max_salary = $job->salary_max;

            $categoryNames = [];
            foreach ($job->job_categories as $job_category)
            {
                $categoryNames [] = $job_category->category->name;
            }


            $skillNames = [];
            foreach ($job->job_skills as $job_skill)
            {
                $skillNames [] = $job_skill->skill->name;
            }

            $applicantCounter = count($job->applicants);
            $acceptApplicants = 0;
            $declineApplicants = 0;

            foreach ($job->applicants as $applicant)
            {
                if ($applicant->status == '2')
                {
                    $acceptApplicants++;
                }elseif ($applicant->status == '3')
                {
                    $declineApplicants++;
                }
            }
            $result =
                [
                    'id' => $job->id,
                    'description_name' => $job->job_description->name,
                    'company_id' => $job->company->id,
                    'company_name' => $job->company->company_name,
                    'company_image' => $job->company->image,
                    'date' => $job->created_at->format('d-m-Y'),
                    'experience_min' => $job->experience_min,
                    'experience_max' => $job->experience_max,
                    'carer_level' => $job->carer_level->name,
                    'isHideSalary' => $job->isHideSalary,
                    'job_details' => $job->job_details,
                    'job_type' => $job->job_type->name,
                    'number_of_vacancies' => $job->number_of_vacancies,
                    'additionSalaryDetails' => $job->additionSalaryDetails,
                    'country' => $job->country,
                    'city' => $job->city,
                    'education_level' => $job->education_level->name,
                    'min_salary' => $min_salary,
                    'max_salary' => $max_salary,
                    'category_names' => $categoryNames,
                    'skill_names' => $skillNames,
                    'applicantCounter' => $applicantCounter,
                    'acceptApplicants' => $acceptApplicants,
                    'declineApplicants' => $declineApplicants,
                ];
            return $this->returnData(['response'], [$result],'Job Data');
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function getApplicants($numOfPage,$numOfRows)
    {
        try {
            $admin = auth('admin')->userOrFail();

            $applicants = Applicant::all();

            $counter = count($applicants);

            $skippedNumbers = ($numOfPage - 1) * $numOfRows;

            $applicants = Applicant::skip($skippedNumbers)
                ->take($numOfRows)
                ->get();
            $applicants = collect($applicants)->map(function($oneRecord)
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

                return
                    [
                        "applicant_id" => $oneRecord->id,
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
                    ];
            });
            $result = [
                'counter'=>$counter,
                'applicants'=>$applicants
            ];
            return $this->returnData(['response'], [$result],'Applicants Data');
        }catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function getApplicantsAnswer($id)
    {
        try {
            $admin = auth('admin')->userOrFail();

            $applicant = Applicant::find($id);
            if(!$applicant )
            {
                return $this->returnError(201, 'Not a applicant');
            }

            $answers = collect($applicant->answers)->map(function($oneRecord)
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

    public function updateCompany(Request $request,$id)
    {
        try {
            $admin = auth('admin')->userOrFail();

            $company = Company::find($id);
            if(!$company )
            {
                return $this->returnError(201, 'Not a Company');
            }

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

    public function updatePerson(Request $request,$id)
    {
        try {
            $admin = auth('admin')->userOrFail();

            $person = Person::find($id);
            if(!$person )
            {
                return $this->returnError(201, 'Not a Person');
            }

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

    public function deleteCompany($id)
    {
        try {
            $admin = auth('admin')->userOrFail();

            $company = Company::find($id);
            if(!$company)
            {
                return $this->returnError(201, 'Not available User');
            }
            $company->delete();
            return $this->returnSuccessMessage('deleted Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function deletePerson($id)
    {
        try {
            $admin = auth('admin')->userOrFail();

            $person= Person::find($id);
            if(!$person)
            {
                return $this->returnError(201, 'Not available');
            }
            $person->delete();
            return $this->returnSuccessMessage('deleted Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function deleteJob($id)
    {
        try {
            $admin = auth('admin')->userOrFail();

            $job= Job::find($id);
            if(!$job)
            {
                return $this->returnError(201, 'Not available');
            }
            $job->delete();
            return $this->returnSuccessMessage('deleted Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function deleteApplicant($id)
    {
        try {
            $admin = auth('admin')->userOrFail();

            $applicant = Applicant::find($id);
            if(!$applicant)
            {
                return $this->returnError(201, 'Not available');
            }
            $applicant->delete();
            return $this->returnSuccessMessage('deleted Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function changePassword(Request $request)
    {
        try {
            $admin = auth('admin')->userOrFail();
            $validator = Validator::make($request->all(), [
                'old_password' => 'required|min:8',
                'password' => 'required|confirmed|min:8',
            ]);
            if ($validator->fails()) {
                return $this->returnValidationError(422, $validator);
            }
            if (! Hash::check($request->old_password, $admin->password)) {

                return $this->returnError(201, 'Wrong Password');
            }
            $admin->password = Hash::make($request->password);
            $admin->save();
            return  $this->returnSuccessMessage('password have been changed',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function blockPerson(Request $request,$id)
    {
        try {
            $admin = auth('admin')->userOrFail();
            $person = Person::find($id);
            if (!$person) {
                return $this->returnError(201, 'invalid id');
            }
            $person->isBlocked = 1;
            $person->save();
            return  $this->returnSuccessMessage('Blocked Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function blockCompany(Request $request,$id)
    {
        try {
            $admin = auth('admin')->userOrFail();
            $company = Company::find($id);
            if (!$company) {
                return $this->returnError(201, 'invalid id');
            }
            $company->isBlocked = 1;
            $company->save();
            return  $this->returnSuccessMessage('Blocked Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function unblockPerson(Request $request,$id)
    {
        try {
            $admin = auth('admin')->userOrFail();
            $person = Person::find($id);
            if (!$person) {
                return $this->returnError(201, 'invalid id');
            }
            $person->isBlocked = 0;
            $person->save();
            return  $this->returnSuccessMessage('unblocked Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function unblockCompany(Request $request,$id)
    {
        try {
            $admin = auth('admin')->userOrFail();
            $company = Company::find($id);
            if (!$company) {
                return $this->returnError(201, 'invalid id');
            }
            $company->isBlocked = 0;
            $company->save();
            return  $this->returnSuccessMessage('unblocked Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function banPerson(Request $request,$id)
    {
        $validator = Validator::make($request->all(), [
            'ban_times' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->returnValidationError(422, $validator);
        }
        try {
            $admin = auth('admin')->userOrFail();
            $person = Person::find($id);
            if (!$person) {
                return $this->returnError(201, 'invalid id');
            }
            $person->ban_times = $request->ban_times;
            $person->save();
            return  $this->returnSuccessMessage('Banned Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function banCompany(Request $request,$id)
    {
        $validator = Validator::make($request->all(), [
            'ban_times' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->returnValidationError(422, $validator);
        }
        try {
            $admin = auth('admin')->userOrFail();
            $company = Company::find($id);
            if (!$company) {
                return $this->returnError(201, 'invalid id');
            }
            $company->ban_times = $request->ban_times;
            $company->save();
            return  $this->returnSuccessMessage('Banned Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function unbanPerson(Request $request,$id)
    {
        try {
            $admin = auth('admin')->userOrFail();
            $person = Person::find($id);
            if (!$person) {
                return $this->returnError(201, 'invalid id');
            }
            $person->ban_times = 0;
            $person->save();
            return  $this->returnSuccessMessage('unBanned Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function unbanCompany(Request $request,$id)
    {
        try {
            $admin = auth('admin')->userOrFail();
            $company = Company::find($id);
            if (!$company) {
                return $this->returnError(201, 'invalid id');
            }
            $company->ban_times = 0;
            $company->save();
            return  $this->returnSuccessMessage('unBanned Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
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
