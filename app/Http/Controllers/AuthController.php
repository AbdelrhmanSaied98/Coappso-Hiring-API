<?php

namespace App\Http\Controllers;

use App\Mail\sendingForgetPassword;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Company;
use App\Models\Company_Category;
use App\Models\Job_Description;
use App\Models\Job_Search_Status;
use App\Models\Job_Study_Field;
use App\Models\Job_Type;
use App\Models\Language;
use App\Models\Notification;
use App\Models\Person;
use App\Models\Person_Category;
use App\Models\Person_Job_Description;
use App\Models\Person_Job_Type;
use App\Models\Person_Language;
use App\Models\Person_Skill;
use App\Models\Person_Study_Field;
use App\Models\Skills;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:"person","company"'
        ]);
        if ($validator->fails()) {
            return $this->returnValidationError(422, $validator);
        }
        if($request->type == 'person')
        {
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|min:3|max:255',
                'last_name' => 'required|string|min:3|max:255',
                'phone' => 'required|string|min:9|unique:persons|unique:companies',
                'email' => 'required|string|email|min:5|max:255|unique:persons|unique:companies',
                'password' => 'required|string|min:8',
                'device_token'=> 'required|string',
                'birth_date'=> 'required|date_format:Y-m-d',
                'gender'=> 'required|in:"Male","Female"',
                'country' => 'required|string',
                'city' => 'required|string',
                'min_salary' => 'required|numeric',
                'isHideSalary'=> 'required|in:0,1',
                'carer_level_id'=>'required|exists:carer_levels,id',
                'experience_year_id'=>'required|exists:experience_years,id',
                'education_level_id'=>'required|exists:education_levels,id',
                'job_search_status_id'=>'required|exists:job_search_status,id',
                'job_types'=> 'required|string',
                'job_descriptions'=> 'required|string',
                'job_categories'=> 'required|string',
                'skills'=> 'required|string',
                'person_languages'=> 'required|string',
                'proficiency'=> 'required|string',
                'nationality'=> 'required|string',

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

            $job_typesArray = json_decode($request->job_types);




            foreach ($job_typesArray as $item)
            {
                $type = Job_Type::find($item);

                if (!$type)
                {
                    return $this->returnError(201, 'Not job type id');
                }
            }



            $job_descriptionsArray = json_decode($request->job_descriptions);



            foreach ($job_descriptionsArray as $item)
            {
                $Job_Description= Job_Description::where('name',$item)->first();
                if (!$Job_Description)
                {
                    return $this->returnError(201, 'Not Job Description id');
                }
            }





            $job_categoriesArray = json_decode($request->job_categories);


            foreach ($job_categoriesArray as $item)
            {
                $Job_Category= Category::where('name',$item)->first();
                if (!$Job_Category)
                {
                    return $this->returnError(201, 'Not Job Category id');
                }
            }


            $skillsArray = json_decode($request->skills);

            foreach ($skillsArray as $item)
            {
                $Skill= Skills::where('name',$item)->first();
                if (!$Skill)
                {
                    return $this->returnError(201, 'Not Skill id');
                }
            }






            $person_languagesArray = json_decode($request->person_languages);

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





            $newPerson = new Person;
            $newPerson->first_name = $request->first_name;
            $newPerson->last_name = $request->last_name;
            $newPerson->phone = $request->phone;
            $newPerson->email = $request->email;
            $newPerson->password = Hash::make($request->password);
            $newPerson->device_token = $request->device_token;
            $newPerson->birth_date = $request->birth_date;
            $newPerson->gender = $request->gender;
            $newPerson->country = $request->country;
            $newPerson->city = $request->city;
            $newPerson->isHideSalary = $request->isHideSalary;
            $newPerson->min_salary = $request->min_salary;
            $newPerson->carer_level_id = $request->carer_level_id;
            $newPerson->experience_year_id = $request->experience_year_id;
            $newPerson->job_search_status_id = $request->job_search_status_id;
            $newPerson->nationality = $request->nationality;

            if($request->cv && $request->cv != "")
            {
                $validator = Validator::make($request->all(), [
                    'cv' => 'required|file',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $cv = $this->uploadImage($request,'cvs','cv');
                $newPerson->cv = $cv;
            }

            $newPerson->save();


            foreach ($job_typesArray as $item)
            {
                $job_type = Job_Type::find($item);
                $newPerson_Job_Type = new Person_Job_Type;
                $newPerson_Job_Type->person_id = $newPerson->id;
                $newPerson_Job_Type->job_type_id = $job_type->id;
                $newPerson_Job_Type->save();
            }

            foreach ($job_descriptionsArray as $item)
            {
                $Job_Description= Job_Description::where('name',$item)->first();
                $newPersonJobDescription = new Person_Job_Description;
                $newPersonJobDescription->person_id = $newPerson->id;
                $newPersonJobDescription->job_description_id = $Job_Description->id;
                $newPersonJobDescription->save();
            }

            foreach ($job_categoriesArray as $item)
            {
                $Job_Category= Category::where('name',$item)->first();
                $newPerson_Category = new Person_Category;
                $newPerson_Category->person_id = $newPerson->id;
                $newPerson_Category->category_id = $Job_Category->id;
                $newPerson_Category->save();
            }



            foreach ($skillsArray as $item)
            {
                $Skill= Skills::where('name',$item)->first();
                $newPerson_Skill = new Person_Skill;
                $newPerson_Skill->person_id = $newPerson->id;
                $newPerson_Skill->skill_id = $Skill->id;
                $newPerson_Skill->save();
            }



            for ($i = 0 ; $i < count($proficiencyArray) ; $i++)
            {
                $Language= Language::find($person_languagesArray[$i]);
                $newPerson_Language = new Person_Language;
                $newPerson_Language->person_id = $newPerson->id;
                $newPerson_Language->language_id = $Language->id;
                $newPerson_Language->proficiency = $proficiencyArray[$i];
                $newPerson_Language->save();
            }


            if($isHighSchool)
            {
                $newPersonStudyField = new Person_Study_Field;
                $newPersonStudyField->education_level_id = $request->education_level_id;
                $newPersonStudyField->person_id = $newPerson->id;
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
                $newPersonStudyField->person_id = $newPerson->id;
                $newPersonStudyField->job_study_field_id = $job_Field->id;
                $newPersonStudyField->institution_name = $request->institution_name;
                $newPersonStudyField->graduation_year = $request->graduation_year;
                $newPersonStudyField->grade_id = $request->grade_id;
                $newPersonStudyField->save();
            }



            $credentials = request(['email', 'password']);
            $token = auth('person')->setTTL(5)->attempt($credentials);

            $tokenRefresh = auth('person')->setTTL(1440)->attempt($credentials);
            $newPerson->refresh_token = $tokenRefresh;
            $newPerson->save();

            return $this->respondWithToken($token,$tokenRefresh,$newPerson);

        }elseif ($request->type == 'company') {
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|min:3|max:255',
                'last_name' => 'required|string|min:3|max:255',
                'phone' => 'required|string|min:9|unique:persons|unique:companies',
                'email' => 'required|string|email|min:5|max:255|unique:persons|unique:companies',
                'password' => 'required|string|min:8',
                'device_token' => 'required|string',
                'company_name' => 'required|string',
                'title' => 'required|string',
                'company_size' => 'required|string',
                'company_categories' => 'required|string',
                'country' => 'required|string',
                'city' => 'required|string',

            ]);
            if ($validator->fails()) {
                return $this->returnValidationError(422, $validator);
            }


            $company_categoriesArray = json_decode($request->company_categories);

            foreach ($company_categoriesArray as $item) {
                $type = Category::find($item);
                if (!$type) {
                    return $this->returnError(201, 'Not job Category id');
                }
            }


            $newCompany = new Company;
            $newCompany->first_name = $request->first_name;
            $newCompany->last_name = $request->last_name;
            $newCompany->phone = $request->phone;
            $newCompany->email = $request->email;
            $newCompany->password = Hash::make($request->password);
            $newCompany->device_token = $request->device_token;
            $newCompany->company_name = $request->company_name;
            $newCompany->company_size = $request->company_size;
            $newCompany->title = $request->title;
            $newCompany->country = $request->country;
            $newCompany->city = $request->city;
            $newCompany->save();


            foreach ($company_categoriesArray as $item) {
                $type = Category::find($item);
                $newCompany_Category = new Company_Category;
                $newCompany_Category->company_id = $newCompany->id;
                $newCompany_Category->category_id = $type->id;
                $newCompany_Category->save();
            }


            $credentials = request(['email', 'password']);
            $token = auth('company')->setTTL(5)->attempt($credentials);
            $tokenRefresh = auth('company')->setTTL(1440)->attempt($credentials);
            $newCompany->refresh_token = $tokenRefresh;
            $newCompany->save();
            return $this->respondWithToken($token,$tokenRefresh, $newCompany);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:"person","company"',
            'device_token'=>'required|string',
            'isRemembered'=>'required|in:0,1',
        ]);
        if ($validator->fails()) {
            return $this->returnValidationError(422, $validator);
        }
        if($request->type == 'person')
        {
            $credentials = request(['email', 'password']);
            $user = null;
            if (! $token = auth('person')->setTTL(5)->attempt($credentials)) {
                if(! $token = auth('person')->setTTL(5)->attempt(['phone' => $request->email, 'password' => $request->password]))
                {
                    return response()->json(['error' => 'Unauthorized'], 401);
                }
                $user = Person::where('phone',$request->email)->first();
            }
            if(! $user)
            {
                $user = Person::where('email',$request->email)->first();
            }

            if ($request->isRemembered)
            {
                $tokenRefresh = auth('person')->setTTL(259200)->attempt($credentials);
            }else
            {
                $tokenRefresh = auth('person')->setTTL(1440)->attempt($credentials);
            }

        }elseif ($request->type == 'company')
        {
            $credentials = request(['email', 'password']);
            $user = null;

            if ( $token = auth('admin')->setTTL(1440)->attempt($credentials)) {
                $user = Admin::where('email',$request->email)->first();
                $user->device_token = $request->device_token;
                $user->save();
                $user->isAdmin = 1;
                return $this->respondWithToken($token,"",$user);
            }


            if (! $token = auth('company')->setTTL(5)->attempt($credentials)) {
                if(! $token = auth('company')->setTTL(5)->attempt(['phone' => $request->email, 'password' => $request->password]))
                {
                    return response()->json(['error' => 'Unauthorized'], 401);
                }
                $user = Company::where('phone',$request->email)->first();
            }
            if(! $user)
            {
                $user = Company::where('email',$request->email)->first();
            }

            if ($request->isRemembered)
            {
                $tokenRefresh = auth('company')->setTTL(259200)->attempt($credentials);
            }else
            {
                $tokenRefresh = auth('company')->setTTL(1440)->attempt($credentials);
            }
        }
        $user->device_token = $request->device_token;
        $user->refresh_token = $tokenRefresh;
        $user->save();
        $user->isAdmin = 0;


        if ($user->isBlocked != 0)
        {
            return $this->returnError(400, 'you have been blocked');
        }
        if($user->ban_times != 0)
        {
            return $this->returnError(400, 'you have been banned for '.$user->ban_times .' days');
        }

        return $this->respondWithToken($token,$tokenRefresh,$user);
    }

    public function logout(Request $request)
    {

        if($request->header('type') == 'person')
        {
            try {
                $user = auth('person')->userOrFail();
                if($request->device_token == $user->device_token)
                {
                    $user->device_token = "";
                    $user->save();
                }
                auth('person')->logout();
                return response()->json(['message' => 'Successfully logged out']);
            } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }elseif($request->header('type') == 'company')
        {
            try {
                $user = auth('company')->userOrFail();
                if($request->device_token == $user->device_token)
                {
                    $user->device_token = "";
                    $user->save();
                }
                auth('company')->logout();
                return response()->json(['message' => 'Successfully logged out']);
            } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }elseif($request->header('type') == 'admin')
        {
            try {
                $user = auth('admin')->userOrFail();
                if($request->device_token == $user->device_token)
                {
                    $user->device_token = "";
                    $user->save();
                }
                auth('admin')->logout();
                return response()->json(['message' => 'Successfully logged out']);
            } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }
    }

    public function profile($type,$id)
    {
        if($type == 'person')
        {
            try {
                $person = Person::find($id);
                if(!$person)
                {
                    return $this->returnError(201, 'Not a Person');
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
                            'id' => $person_language->id,
                            'language_id' => $person_language->language->id,
                            'name' => $person_language->language->name,
                            'proficiency' => $person_language->proficiency,
                        ];
                    $personLanguageNames [] = $object;
                }

                try {
                    $patient = auth('person')->userOrFail();
                    $enableSalary = true;
                } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
                    $enableSalary = false;
                }

                $min_salary = null;
                if($enableSalary)
                {
                    $min_salary = $person->min_salary;
                }
                if($person->isHideSalary == 0)
                {
                    $min_salary = $person->min_salary;
                }

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
        }elseif($type == 'company')
        {
            try {
                $company = Company::find($id);
                if(!$company)
                {
                    return $this->returnError(201, 'Not a company');
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
    }

    public function getNotification(Request $request,$numOfPage,$numOfRows)
    {
        if($request->header('type') == 'company')
        {
            try {
                $company = auth('company')->userOrFail();

                if($company->ban_times != 0)
                {
                    return $this->returnError(400, 'you have been banned for '.$company->ban_times.' days');
                }
                if($company->isBlocked == 1)
                {
                    return $this->returnError(400, 'you have been blocked');
                }

                $notifications = Notification::where('user_type','company')->where('user_id',$company->id)->orderBy('created_at', 'DESC')
                    ->get();
                $counter = count($notifications);
                $skippedNumbers = ($numOfPage - 1) * $numOfRows;
                $notifications = Notification::where('user_type','company')->where('user_id',$company->id)->orderBy('created_at', 'DESC')
                    ->skip($skippedNumbers)
                    ->take($numOfRows)
                    ->get();
                Notification::where('user_type','company')->where('user_id',$company->id)->orderBy('created_at', 'DESC')
                    ->update(['seen' => 1]);
                $notifications = collect($notifications)->map(function($oneNotification)
                {
                    $notificationDate = $oneNotification->created_at->format('Y-m-d');
                    $currentDate = Carbon::now();
                    $currentDate = $currentDate->toDateString();
                    if($currentDate == $notificationDate)
                    {
                        $date = 'Today';
                    }else
                    {
                        $date = $notificationDate;
                    }
                    return
                        [
                            "content_type" => $oneNotification->content_type,
                            "notification" => $oneNotification->notification,
                            "content_id" => $oneNotification->content_id,
                            "seen" => $oneNotification->seen,
                            "created_at" => date('g:i A', strtotime($oneNotification->created_at)),
                            "date" => $date,
                        ];
                });
                $result = [
                    'notification' => $notifications,
                    'length' =>$counter
                ];
                return $this->returnData(['response'], [$result],'Notifications Data');
            } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }elseif($request->header('type') == 'person')
        {
            try {
                $person = auth('person')->userOrFail();

                if($person->ban_times != 0)
                {
                    return $this->returnError(400, 'you have been banned for '.$person->ban_times.' days');
                }
                if($person->isBlocked == 1)
                {
                    return $this->returnError(400, 'you have been blocked');
                }

                $notifications = Notification::where('user_type','person')->where('user_id',$person->id)->orderBy('created_at', 'DESC')
                    ->get();
                $counter = count($notifications);
                $skippedNumbers = ($numOfPage - 1) * $numOfRows;
                $notifications = Notification::where('user_type','person')->where('user_id',$person->id)->orderBy('created_at', 'DESC')
                    ->skip($skippedNumbers)
                    ->take($numOfRows)
                    ->get();
                Notification::where('user_type','person')->where('user_id',$person->id)->orderBy('created_at', 'DESC')
                    ->update(['seen' => true]);
                $notifications = collect($notifications)->map(function($oneNotification)
                {
                    $notificationDate = $oneNotification->created_at->format('Y-m-d');
                    $currentDate = Carbon::now();
                    $currentDate = $currentDate->toDateString();
                    if($currentDate == $notificationDate)
                    {
                        $date = 'Today';
                    }else
                    {
                        $date = $notificationDate;
                    }
                    return
                        [
                            "content_type" => $oneNotification->content_type,
                            "notification" => $oneNotification->notification,
                            "content_id" => $oneNotification->content_id,
                            "seen" => $oneNotification->seen,
                            "created_at" => date('g:i A', strtotime($oneNotification->created_at)),
                            "date" => $date,
                        ];

                });
                $result = [
                    'notification' => $notifications,
                    'length' =>$counter
                ];
                return $this->returnData(['response'], [$result],'Notifications Data');
            } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }
    }

    public function uploadProfileImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|file',
        ]);
        if ($validator->fails()) {
            return $this->returnValidationError(422, $validator);
        }
        if($request->header('type') == 'person')
        {
            try {
                $user = auth('person')->userOrFail();

                if($user->image)
                {
                    $path =  public_path('/assets/persons/'.$user->image);
                    $image_path = $path;
                    if(File::exists($image_path)) {
                        File::delete($image_path);
                    }
                }
                $image = $this->uploadImage($request,'persons','image');
                $user->image = $image;
                $user->save();
                return response()->json(['message' => 'Successfully Uploaded']);
            } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }elseif ($request->header('type') == 'company')
        {
            try {
                $user = auth('company')->userOrFail();

                if($user->image)
                {
                    $path =  public_path('/assets/companies/'.$user->image);
                    $image_path = $path;
                    if(File::exists($image_path)) {
                        File::delete($image_path);
                    }
                }
                $image = $this->uploadImage($request,'companies','image');
                $user->image = $image;
                $user->save();
                return response()->json(['message' => 'Successfully Uploaded']);
            } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }
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

    public function getBaniAdam()
    {
        try {
            $person = auth('person')->userOrFail();
            if($person->isBlocked != 0)
            {
                $result = 2;
            }elseif ($person->ban_times != 0)
            {
                $result = 3;
            }else
            {
                $result = 1;
            }
            return $this->returnData(['response'], [$result],'BaniAdam data');
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            try {
                $company = auth('company')->userOrFail();
                if($company->isBlocked != 0)
                {
                    $result = 2;
                }elseif ($company->ban_times != 0)
                {
                    $result = 3;
                }else
                {
                    $result = 1;
                }
                return $this->returnData(['response'], [$result],'BaniAdam data');
            } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }
    }

    public function getNewToken(Request $request,$type)
    {
        if ($type == "person")
        {
            try {
                $person = auth('person')->userOrFail();

                $token = auth('person')->setTTL(5)->login($person);


                return $this->returnData(['response'], [$token],'Token');
            } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }elseif ($type == "company")
        {

            try {
                $company = auth('company')->userOrFail();

                $token = auth('company')->setTTL(5)->login($company);


                return $this->returnData(['response'], [$token],'Token');
            } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

        }
    }

    public function testAuth()
    {
        try {
            $company = auth('company')->userOrFail();
            return $company;
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function forgetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:"person","company"',
            'email' => 'required|string|email',
        ]);
        if ($validator->fails()) {
            return $this->returnValidationError(422, $validator);
        }

        $type = $request->type;
        if($type == 'person')
        {
            $user = Person::where('email', $request->email)->get();
        }elseif ($type == 'company')
        {
            $user = Company::where('email', $request->email)->get();
        }
        if (count($user) == 1) {
            $rand = mt_rand(10000, 99999);
            $current_times = Carbon::now()->toDateTimeString();
            $objDemo = 'Hello There , Your Activation code is '. $rand;
            Mail::to($user[0]->email)->send(new sendingForgetPassword($objDemo));
            $user[0]->update([
                'verification_code' => $rand,
                'sending_code_time' => $current_times,
            ]);
            return $this->returnSuccessMessage(
                [
                    'msg' => 'Check Your Email And Enter the code'
                ], 200);

        } else {
            return $this->returnError(201, 'Email Not Found');
        }
    }

    public function verifyCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:"person","company"',
            'email' => 'required|string|email',
            'verification_code'=>'required',
        ]);
        if ($validator->fails()) {
            return $this->returnValidationError(422, $validator);
        }

        $type = $request->type;
        if($type == 'person')
        {
            $user = Person::where('email', $request->email)->get();
        }elseif ($type == 'company')
        {
            $user = Company::where('email', $request->email)->get();
        }

        if (count($user) == 1) {
            if($request->verification_code == $user[0]->verification_code)
            {
                $sendTime = Carbon::parse($user[0]->sending_code_time);
                $sendTime->addMinute(2);
                $now = Carbon::now();
                if ($now->greaterThanOrEqualTo($sendTime))
                {
                    return $this->returnError(201, 'Expired Code ');
                }
                return $this->returnSuccessMessage('Go to Next Step',200);
            }else
            {
                return $this->returnError(201, 'verification code is wrong');
            }
        } else {
            return $this->returnError(201, 'Email Not Found');
        }
    }

    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:"person","company"',
            'email' => 'required|string|email',
            'verification_code'=>'required',
            'password' => 'required|confirmed|min:8',
        ]);
        if ($validator->fails()) {
            return $this->returnValidationError(422, $validator);
        }

        $type = $request->type;
        if($type == 'person')
        {
            $user = Person::where('email', $request->email)->get();
        }elseif ($type == 'company')
        {
            $user = Company::where('email', $request->email)->get();
        }

        if (count($user) == 1) {

            if($request->verification_code == $user[0]->verification_code)
            {
                $sendTime = Carbon::parse($user[0]->sending_code_time);
                $sendTime->addMinute(3);
                $now = Carbon::now();
                if ($now->greaterThanOrEqualTo($sendTime))
                {
                    return $this->returnError(201, 'Expired Code ');
                }
                $rand = null;
                $current_times = null;
                $user[0]->verification_code = $rand;
                $user[0]->sending_code_time = $current_times;
                $user[0]->password = Hash::make($request->password);
                $user[0]->save();
                return $this->returnSuccessMessage('Updated Successfully',200);
            }else
            {
                return $this->returnError(201, 'verification code is wrong');
            }
        } else {
            return $this->returnError(201, 'Email Not Found');
        }
    }

    protected function respondWithToken($token,$tokenRefresh,$user)
    {
        if($user->gym_branches)
        {
            $user->main_branch_id = $user->gym_branches[0]->id;
            $result = $user;
            unset(
                $user->gym_branches
            );
        }else
        {
            $result = $user;
        }
        return response()->json([
            'access_token' => $token,
            'refresh_token' => $tokenRefresh,
            'token_type' => 'Bearer',
            'users'=>$result,
            'expires_in' => 'forever'
        ]);
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
