<?php

namespace App\Http\Controllers;


use App\Models\Answer;
use App\Models\Applicant;
use App\Models\Category;
use App\Models\Company_Category;
use App\Models\Favorite;
use App\Models\Job;
use App\Models\Job_Category;
use App\Models\Job_Description;
use App\Models\Job_Form;
use App\Models\Job_Skill;
use App\Models\Language;
use App\Models\Notification;
use App\Models\Person_Language;
use App\Models\Person_Study_Field;
use App\Models\Short_List;
use App\Models\Skills;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class JobController extends Controller
{
    public function store(Request $request)
    {

        try {
            $company = auth('company')->userOrFail();

            $validator = Validator::make($request->all(), [
                'country' => 'required|string',
                'city' => 'required|string',
                'experience_min' => 'required|numeric',
                'experience_max' => 'required|numeric',
                'salary_min' => 'required|numeric',
                'salary_max' => 'required|numeric',
                'carer_level_id'=>'required|exists:carer_levels,id',
                'job_description_id'=>'required|exists:job_descriptions,id',
                'job_type_id'=>'required|exists:job_types,id',
                'isHideSalary'=> 'required|in:0,1',
                'additionSalaryDetails' => 'required|string',
                'number_of_vacancies' => 'required|numeric',
                'job_details' => 'required|string',
                'education_level_id'=>'required|exists:education_levels,id',
                'job_categories'=>'required|string',
                'skills'=> 'required|string',
                'types'=> 'required|string',
                'question_titles'=> 'required|string',
                'chooses'=> 'required|string',
            ]);
            if ($validator->fails()) {
                return $this->returnValidationError(422, $validator);
            }


            $job_categoriesArray = json_decode($request->job_categories);



            foreach ($job_categoriesArray as $item)
            {
                $Job_Category= Category::find($item);
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



            $types = ['text','record','MCQ','CV'];

            $typesArray = json_decode($request->types);


            $counterMCQ = 0;
            foreach ($typesArray as $item)
            {
                if (!in_array($item, $types)) {
                    return $this->returnError(201, 'Not a from question type');
                }

                if ($item == "MCQ") {
                    $counterMCQ ++;
                }
            }


            $question_titlesArray = json_decode($request->question_titles);


            if (count($question_titlesArray) > 5)
            {
                return $this->returnError(201, 'More than 5 Question');
            }


            if(count($question_titlesArray) != count($typesArray))
            {
                return $this->returnError(201, 'types array must be equal to questions array');
            }

            $choosesArray = json_decode($request->chooses);

            if(count($choosesArray) != $counterMCQ)
            {
                return $this->returnError(201, 'chooses array must be MCQ questions types');
            }


            $newJob = new Job;
            $newJob->country = $request->country;
            $newJob->city = $request->city;
            $newJob->experience_min = $request->experience_min;
            $newJob->experience_max = $request->experience_max;
            $newJob->salary_min = $request->salary_min;
            $newJob->salary_max = $request->salary_max;
            $newJob->carer_level_id = $request->carer_level_id;
            $newJob->job_description_id = $request->job_description_id;
            $newJob->job_type_id = $request->job_type_id;
            $newJob->isHideSalary = $request->isHideSalary;
            $newJob->additionSalaryDetails = $request->additionSalaryDetails;
            $newJob->number_of_vacancies = $request->number_of_vacancies;
            $newJob->job_details = $request->job_details;
            $newJob->education_level_id = $request->education_level_id;
            $newJob->company_id = $company->id;
            $newJob->save();



            foreach ($job_categoriesArray as $item)
            {
                $Job_Category= Category::find($item);
                $newJob_Category = new Job_Category;
                $newJob_Category->job_id = $newJob->id;
                $newJob_Category->category_id = $Job_Category->id;
                $newJob_Category->save();
            }

            foreach ($skillsArray as $item)
            {
                $Skill= Skills::where('name',$item)->first();
                $newJob_Skill = new Job_Skill;
                $newJob_Skill->job_id = $newJob->id;
                $newJob_Skill->skill_id = $Skill->id;
                $newJob_Skill->save();

            }

            $newCounterMCQ = 0;
            for ($i = 0 ; $i < count($question_titlesArray) ; $i++)
            {
                $newJob_Form = new Job_Form;
                $newJob_Form->job_id = $newJob->id;
                $newJob_Form->question_title = $question_titlesArray[$i];
                $newJob_Form->type = $typesArray[$i];
                if($typesArray[$i] == "MCQ")
                {
                    $questionChoosesArray = $choosesArray[$newCounterMCQ];
                    $ChoosesString = implode(',',$questionChoosesArray);
                    $newJob_Form->chooses = $ChoosesString;
                    $newCounterMCQ++;
                }
                $newJob_Form->save();
            }

            return $this->returnSuccessMessage('New job has been posted successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

    }

    public function show($id)
    {
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

        $categoryIds = [];
        $categoryNames = [];
        foreach ($job->job_categories as $job_category)
        {
            $categoryIds [] = $job_category->category->id;
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
                'description_id' => $job->job_description->id,
                'company_id' => $job->company->id,
                'company_name' => $job->company->company_name,
                'company_image' => $job->company->image,
                'date' => $job->created_at->format('d-m-Y'),
                'experience_min' => $job->experience_min,
                'experience_max' => $job->experience_max,
                'applicants' => count($job->applicants),
                'carer_level' => $job->carer_level->name,
                'carer_level_id' => $job->carer_level->id,
                'isHideSalary' => $job->isHideSalary,
                'job_details' => $job->job_details,
                'job_type' => $job->job_type->name,
                'number_of_vacancies' => $job->number_of_vacancies,
                'additionSalaryDetails' => $job->additionSalaryDetails,
                'country' => $job->country,
                'city' => $job->city,
                'education_level' => $job->education_level->id,
                'education_level_name' => $job->education_level->name,
                'min_salary' => $min_salary,
                'max_salary' => $max_salary,
                'category_ids' => $categoryIds,
                'category_names' => $categoryNames,
                'skill_names' => $skillNames,
                'applicantCounter' => $applicantCounter,
                'acceptApplicants' => $acceptApplicants,
                'declineApplicants' => $declineApplicants,
            ];
        return $this->returnData(['response'], [$result],'Job Data');
    }

    public function show_for_person($id)
    {
        try {
            $person = auth('person')->userOrFail();
            $job = Job::find($id);
            if(!$job)
            {
                return $this->returnError(201, 'Not a job');
            }

            if($job->company->image)
            {
                $job->company->image = asset('/assets/companies/' . $job->company->image );
            }

            if($job->isHideSalary)
            {
                $min_salary = null;
                $max_salary = null;
            }else
            {
                $min_salary = $job->salary_min;
                $max_salary = $job->salary_max;
            }

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

            $savedJob = Favorite::where('person_id',$person->id)->where('job_id',$job->id)->first();
            if (!$savedJob)
            {
                $isSaved = 0;
            }else
            {
                $isSaved = 1;
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
            $personApplicant = Applicant::where('job_id',$job->id)->where('person_id',$person->id)->first();
            if (!$personApplicant)
            {
                $status = '0';
            }else
            {
                $status = $personApplicant->status;
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
                    'isSaved' => $isSaved,
                    'applicantCounter' => $applicantCounter,
                    'acceptApplicants' => $acceptApplicants,
                    'declineApplicants' => $declineApplicants,
                    'status' => $status,
                ];
            return $this->returnData(['response'], [$result],'Job Data');
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function update(Request $request,$id)
    {
        try {
            $company = auth('company')->userOrFail();

            $job = Job::find($id);
            if(!$job && $job->company->id != $company->id)
            {
                return $this->returnError(201, 'Not a job');
            }

            if(count($job->applicants) != 0)
            {
                return $this->returnError(201, 'users was applied on this job already');
            }

            if($request->job_description_id && $request->job_description_id != "")
            {
                $validator = Validator::make($request->all(), [
                    'job_description_id'=>'required|exists:job_descriptions,id',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $job->job_description_id = $request->job_description_id;
                $job->save();
            }

            if($request->job_type_id && $request->job_type_id != "")
            {
                $validator = Validator::make($request->all(), [
                    'job_type_id'=>'required|exists:job_types,id',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $job->job_type_id = $request->job_type_id;
                $job->save();
            }

            if($request->job_categories && $request->job_categories != "")
            {
                $validator = Validator::make($request->all(), [
                    'job_categories'=>'required|string',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }

                $job_categoriesArray = json_decode($request->job_categories);

                if (count($job_categoriesArray) == 0)
                {
                    return $this->returnError(201, 'empty array');
                }

                foreach ($job_categoriesArray as $item)
                {
                    $Job_Category= Category::find($item);
                    if (!$Job_Category)
                    {
                        return $this->returnError(201, 'Not Job Category id');
                    }
                }

                foreach ($job->job_categories as $job_category) {
                    $job_category->delete();
                }

                foreach ($job_categoriesArray as $item)
                {
                    $Job_Category= Category::find($item);
                    $newJob_Category = new Job_Category;
                    $newJob_Category->job_id = $job->id;
                    $newJob_Category->category_id = $Job_Category->id;
                    $newJob_Category->save();
                }
            }

            if($request->country && $request->country != "")
            {
                $validator = Validator::make($request->all(), [
                    'country' => 'required|string',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $job->country = $request->country;
                $job->save();
            }

            if($request->city && $request->city != "")
            {
                $validator = Validator::make($request->all(), [
                    'city' => 'required|string',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $job->city = $request->city;
                $job->save();
            }

            if($request->carer_level_id && $request->carer_level_id != "")
            {

                $validator = Validator::make($request->all(), [
                    'carer_level_id'=>'required|exists:carer_levels,id',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $job->carer_level_id = $request->carer_level_id;
                $job->save();
            }

            if($request->education_level_id && $request->education_level_id != "")
            {
                $validator = Validator::make($request->all(), [
                    'education_level_id'=>'required|exists:education_levels,id',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $job->education_level_id = $request->education_level_id;
                $job->save();
            }

            if($request->experience_min && $request->experience_min != "")
            {
                $validator = Validator::make($request->all(), [
                    'experience_min' => 'required|numeric',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $job->experience_min = $request->experience_min;
                $job->save();
            }

            if($request->experience_max && $request->experience_max != "")
            {
                $validator = Validator::make($request->all(), [
                    'experience_max' => 'required|numeric',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $job->experience_max = $request->experience_max;
                $job->save();
            }

            if($request->salary_min && $request->salary_min != "")
            {
                $validator = Validator::make($request->all(), [
                    'salary_min' => 'required|numeric',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $job->salary_min = $request->salary_min;
                $job->save();
            }

            if($request->salary_max && $request->salary_max != "")
            {
                $validator = Validator::make($request->all(), [
                    'salary_max' => 'required|numeric',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $job->salary_max = $request->salary_max;
                $job->save();
            }

            if($request->isHideSalary == '0' || $request->isHideSalary && $request->isHideSalary != "")
            {
                $validator = Validator::make($request->all(), [
                    'isHideSalary'=> 'required|in:0,1',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $job->isHideSalary = $request->isHideSalary;
                $job->save();
            }

            if($request->additionSalaryDetails && $request->additionSalaryDetails != "")
            {
                $validator = Validator::make($request->all(), [
                    'additionSalaryDetails' => 'required|string',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $job->additionSalaryDetails = $request->additionSalaryDetails;
                $job->save();
            }

            if($request->number_of_vacancies && $request->number_of_vacancies != "")
            {
                $validator = Validator::make($request->all(), [
                    'number_of_vacancies' => 'required|numeric',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $job->number_of_vacancies = $request->number_of_vacancies;
                $job->save();
            }

            if($request->job_details && $request->job_details != "")
            {
                $validator = Validator::make($request->all(), [
                    'job_details' => 'required|string',
                ]);
                if ($validator->fails()) {
                    return $this->returnValidationError(422, $validator);
                }
                $job->job_details = $request->job_details;
                $job->save();
            }

            if($request->skills && $request->skills != "")
            {
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


                foreach ($job->job_skills as $job_skill) {
                    $job_skill->delete();
                }


                foreach ($skillsArray as $item)
                {
                    $Skill= Skills::where('name',$item)->first();
                    $newJob_Skill = new Job_Skill;
                    $newJob_Skill->job_id = $job->id;
                    $newJob_Skill->skill_id = $Skill->id;
                    $newJob_Skill->save();
                }
            }

            return $this->returnSuccessMessage('Updated Successfully',200);
        }catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function getQuestions(Request $request,$id)
    {
        try {
            $company = auth('company')->userOrFail();

            $job = Job::find($id);
            if(!$job && $job->company->id != $company->id)
            {
                return $this->returnError(201, 'Not a job');
            }

            $job_forms = collect($job->job_forms)->map(function($oneRecord) use ($company,$job)
            {
                $answersMCQ = null;
                if ($oneRecord->type == 'MCQ')
                {
                    $answersMCQ = explode(',',$oneRecord->chooses);
                }
                return
                    [
                        "job_id" => $job->id,
                        "question_id" => $oneRecord->id,
                        "question_type" => $oneRecord->type,
                        "question_title" => $oneRecord->question_title,
                        "answersMCQ" => $answersMCQ,
                    ];
            });
            return $this->returnData(['response'], [$job_forms],'Jobs Form');
        }catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function getForm(Request $request,$id)
    {
        try {
            $person = auth('person')->userOrFail();

            $job = Job::find($id);
            if(!$job)
            {
                return $this->returnError(201, 'Not a job');
            }
            $texts = [];
            $NonTexts = [];
            $lengthOfText = 0;
            $lengthOfMCQ = 0;
            $lengthOfRecord = 0;
            $lengthOfCV = 0;
            foreach ($job->job_forms as $job_form)
            {
                if ($job_form->type == "text")
                {
                    $lengthOfText++;
                    $texts [] = $job_form;
                }else
                {
                    $NonTexts [] = $job_form;

                    if ($job_form->type == "MCQ")
                    {
                        $lengthOfMCQ++;
                    }elseif ($job_form->type == "CV")
                    {
                        $lengthOfCV++;
                    }elseif ($job_form->type == "record")
                    {
                        $lengthOfRecord++;
                    }
                }


            }
            $texts = collect($texts)->map(function($oneRecord) use ($job)
            {
                $answersMCQ = [];
                if ($oneRecord->type == 'MCQ')
                {
                    $answersMCQ = explode(',',$oneRecord->chooses);
                }

                return
                    [
                        "job_id" => $job->id,
                        "question_id" => $oneRecord->id,
                        "question_type" => $oneRecord->type,
                        "question_title" => $oneRecord->question_title,
                        "answersMCQ" => $answersMCQ,
                    ];
            });



            $NonTexts = collect($NonTexts)->map(function($oneRecord) use ($job)
            {
                $answersMCQ = [];
                if ($oneRecord->type == 'MCQ')
                {
                    $answersMCQ = explode(',',$oneRecord->chooses);
                }

                return
                    [
                        "job_id" => $job->id,
                        "question_id" => $oneRecord->id,
                        "question_type" => $oneRecord->type,
                        "question_title" => $oneRecord->question_title,
                        "answersMCQ" => $answersMCQ,
                    ];
            });



            $allQuestion = collect($job->job_forms)->map(function($oneRecord) use ($job)
            {
                $answersMCQ = [];
                if ($oneRecord->type == 'MCQ')
                {
                    $answersMCQ = explode(',',$oneRecord->chooses);
                }

                return
                    [
                        "job_id" => $job->id,
                        "question_id" => $oneRecord->id,
                        "question_type" => $oneRecord->type,
                        "question_title" => $oneRecord->question_title,
                        "answersMCQ" => $answersMCQ,
                    ];
            });



            $result =
                [
                  'texts' => $texts,
                  'NonTexts' => $NonTexts,
                  'text_length' => $lengthOfText,
                  'mcq_length' => $lengthOfMCQ,
                  'cv_length' => $lengthOfCV,
                  'record_length' => $lengthOfRecord,
                  'allQuestion' => $allQuestion,
                ];
            return $this->returnData(['response'], [$result],'Jobs Form');
        }catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function addQuestion(Request $request,$id)
    {
        try {
            $company = auth('company')->userOrFail();

            $job = Job::find($id);
            if(!$job && $job->company->id != $company->id)
            {
                return $this->returnError(201, 'Not a job');
            }

            if(count($job->applicants) != 0)
            {
                return $this->returnError(201, 'users was applied on this job already');
            }

            $validator = Validator::make($request->all(), [
                'type'=> 'required|in:"text","record","MCQ","CV"',
                'question_title'=> 'required|string',
                'chooses'=> 'string',
            ]);

            if ($validator->fails()) {
                return $this->returnValidationError(422, $validator);
            }

            $newJob_Form = new Job_Form;
            $newJob_Form->job_id = $job->id;
            $newJob_Form->question_title = $request->question_title;
            $newJob_Form->type = $request->type;


            if($request->type == "MCQ")
            {
                $questionChoosesArray = json_decode($request->chooses);
                $ChoosesString = implode(',',$questionChoosesArray);
                $newJob_Form->chooses = $ChoosesString;
            }

            
            $newJob_Form->save();
            return  $this->returnSuccessMessage('Added Successfully',200);
        }catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function deleteQuestion(Request $request,$id)
    {
        try {
            $company = auth('company')->userOrFail();

            $Job_Form = Job_Form::find($id);
            if(!$Job_Form && $Job_Form->job->company->id != $company->id)
            {
                return $this->returnError(201, 'Not a Job Question');
            }
            if(count($Job_Form->job->applicants) != 0)
            {
                return $this->returnError(201, 'users was applied on this job already');
            }
            if (count($Job_Form->job->job_forms) <= 1)
            {
                return $this->returnError(201, 'Not allow to delete');
            }
            $Job_Form->delete();
            return  $this->returnSuccessMessage('Deleted Successfully',200);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function destroy($id)
    {
        try {
            $company = auth('company')->userOrFail();

            $job = Job::find($id);
            if(!$job && $job->company->id != $company->id)
            {
                return $this->returnError(201, 'Not a job');
            }


            foreach ($job->applicants as $applicant)
            {
                $newNotification = new Notification;
                $newNotification->user_type = 'person';
                $newNotification->user_id = $applicant->person->id;
                $newNotification->content_type = 'job';
                $newNotification->content_id = $job->id;
                $newNotification->seen = 0;
                $newNotification->notification = $applicant->job->company->company_name.' has closed '.$job->job_description->name.' job';
                $newNotification->save();

                (new MessageController())->NotifyApi(
                    $applicant->person->device_token,
                    "Job Closed",
                    $applicant->job->company->company_name.' has closed '.$job->job_description->name.' job'
                );
            }

            $job->delete();

            return $this->returnSuccessMessage('Deleted Successfully',200);
        }catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function applyJob(Request $request,$id)
    {
        try {
            $person = auth('person')->userOrFail();

            $job = Job::find($id);
            if(!$job)
            {
                return $this->returnError(201, 'Not a job');
            }
            $validator = Validator::make($request->all(), [
                'questions'=> 'required|string',
                'answers'=> 'string',
                'date' => 'required|date_format:Y-m-d',
            ]);
            if ($validator->fails()) {
                return $this->returnValidationError(422, $validator);
            }

            $questionsArray = json_decode($request->questions);

            $allQuestions = $job->job_forms;

            $answersArray = json_decode($request->answers);



            $files = $request->file('files');

            if(!$files)
            {
                $files = [];
            }

            if(!$answersArray)
            {
                $answersArray = [];
            }


            if(count($questionsArray) != count($allQuestions) || count($allQuestions) != count($answersArray) + count($files))
            {
                return $this->returnError(201, 'Questions array must be equal to Answers array');
            }

            foreach ($questionsArray as $oneQuestion)
            {
                $oneJobForm = Job_Form::find($oneQuestion);
                if(!$oneJobForm)
                {
                    return $this->returnError(201, 'Not Question id');
                }
                if ($oneJobForm->job->id != $job->id)
                {
                    return $this->returnError(201, 'Not this job question');
                }
            }

            $oldApplicant = Applicant::where('person_id',$person->id)
                ->where('job_id',$job->id)
                ->first();
            if ($oldApplicant)
            {
                return $this->returnError(201, 'already applied on this job!');
            }

            $newApplicant = new Applicant();
            $newApplicant->person_id = $person->id;
            $newApplicant->job_id = $job->id;
            $newApplicant->date = $request->date;
            $newApplicant->status = '1';
            $newApplicant->save();


            $fileCounter = 0;
            $textCounter = 0;

            for ($i = 0 ; $i < count($questionsArray) ; $i++)
            {
                $newAnswer = new Answer();
                $newAnswer->applicant_id = $newApplicant->id;
                $oneJobForm = Job_Form::find($questionsArray[$i]);

                if ($oneJobForm->type == 'CV' || $oneJobForm->type == 'record')
                {
                    $files = $request->file('files');
                    if ($oneJobForm->type == 'CV')
                    {

                        $fileextension = $files[$fileCounter]->getClientOriginalExtension();

                        $rand = rand();
                        $filename = $files[$fileCounter]->getClientOriginalName();
                        $file_to_store = time() . '_'.$rand . '_.' . $fileextension;

                        $test = $files[$fileCounter]->move(public_path('assets/appliedCVS'), $file_to_store);
                        if ($test) {
                            $file = $file_to_store;
                        }

                        $newAnswer->answer = $file;
                    }else
                    {
                        $fileextension = $files[$fileCounter]->getClientOriginalExtension();

                        $rand = rand();
                        $filename = $files[$fileCounter]->getClientOriginalName();
                        $file_to_store = time() .$rand . '_.' . $fileextension;

                        $test = $files[$fileCounter]->move(public_path('assets/records'), $file_to_store);
                        if ($test) {
                            $file = $file_to_store;
                        }

                        $newAnswer->answer = $file;
                    }
                    $fileCounter ++;
                }else
                {
                    $newAnswer->answer = $answersArray[$textCounter];
                    $textCounter++;
                }
                $newAnswer->job_form_id = $questionsArray[$i];
                $newAnswer->save();
            }



            $newNotification = new Notification;
            $newNotification->user_type = 'company';
            $newNotification->user_id = $job->company->id;
            $newNotification->content_type = 'job';
            $newNotification->content_id = $job->id;
            $newNotification->seen = 0;
            $newNotification->notification = $person->first_name.' '.$person->last_name.' has applied on '.$job->job_description->name.' job';
            $newNotification->save();

            (new MessageController())->NotifyApi(
                $job->company->device_token,
                "New Applicant",
                $person->first_name.' '.$person->last_name.' has applied on '.$job->job_description->name.' job'
            );

            return $this->returnSuccessMessage('applied successfully',200);
        }catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function searchJob(Request $request,$name)
    {
        try {
            $person = auth('person')->userOrFail();


            if ($name == 'all')
            {
                $job_descriptions = Job_Description::all();
            }else
            {
                $job_descriptions = Job_Description::where(function($query) use ($name) {
                    $query->where('name', 'LIKE','%'.$name.'%');
                })->get();
            }

            $jobs = [];

            foreach ($job_descriptions as $job_description)
            {
                $jobsArray = Job::where('job_description_id',$job_description->id)->get();
                foreach ($jobsArray as $item)
                {
                    $jobs [] = $item->id;
                }
            }

            if ($request->country) {
                $jobs_country = Job::where('country',$request->country)->get();
                foreach ($jobs_country as $record) {
                    $jobs [] = $record->id;
                }
            }

            if ($request->city) {
                $jobs_city = Job::where('city',$request->city)->get();
                foreach ($jobs_city as $record) {
                    $jobs [] = $record->id;
                }
            }

            if ($request->career_levels && $request->career_levels != '[]') {

                $career_levelsArray = json_decode($request->career_levels);

                foreach ($career_levelsArray as $item)
                {
                    $jobs_career_level = Job::where('carer_level_id',$item)->get();
                    foreach ($jobs_career_level as $record) {
                        $jobs [] = $record->id;
                    }
                }
            }

            if ($request->years_of_experience  && $request->years_of_experience[0] != null && $request->years_of_experience[1] != null ) {
                $jobs_years_of_experience = Job::where('experience_min', '>=', $request->years_of_experience[0])->where('experience_max', '<=', $request->years_of_experience[1])->select('id')->get();
                foreach ($jobs_years_of_experience as $record) {
                    $jobs [] = $record->id;
                }
            }

            if ($request->job_types && $request->job_types != '[]') {

                $job_typesArray = json_decode($request->job_types);

                foreach ($job_typesArray as $item)
                {
                    $jobs_types = Job::where('job_type_id',$item)->get();
                    foreach ($jobs_types as $record) {
                        $jobs [] = $record->id;
                    }
                }
            }

            if ($request->date_posted) {

                switch ($request->date_posted)
                {
                    case 1:
                        $allJobs = Job::all();
                        foreach ($allJobs as $job)
                        {
                            $now = Carbon::now();
                            $dateOfJob = $job->created_at->format('Y-m-d');
                            $carbonDateOfJob = Carbon::parse($dateOfJob);
                            $diff_in_days = $now->diffInDays($carbonDateOfJob);
                            if($diff_in_days <= 1)
                            {
                                $jobs [] = $job->id;
                            }
                        }
                        break;
                    case 2:
                        $allJobs = Job::all();
                        foreach ($allJobs as $job)
                        {
                            $now = Carbon::now();
                            $dateOfJob = $job->created_at->format('Y-m-d');
                            $carbonDateOfJob = Carbon::parse($dateOfJob);
                            $diff_in_days = $now->diffInDays($carbonDateOfJob);
                            if($diff_in_days <= 7)
                            {
                                $jobs [] = $job->id;
                            }
                        }
                        break;
                    case 3:
                        $allJobs = Job::all();
                        foreach ($allJobs as $job)
                        {
                            $now = Carbon::now();
                            $dateOfJob = $job->created_at->format('Y-m-d');
                            $carbonDateOfJob = Carbon::parse($dateOfJob);
                            $diff_in_days = $now->diffInDays($carbonDateOfJob);
                            if($diff_in_days <= 30)
                            {
                                $jobs [] = $job->id;
                            }
                        }
                        break;
                }
            }

            $result = [];
            $counter = 1;
            $data = $request->all();
            foreach ($data as $key => $value) {
                if(is_array($value))
                {
                    if($value[0] == null || $value[1] == null)
                    {
                        continue;
                    }
                }
                if($value == null || $value == "" || $value == '[]')
                {
                    continue;
                }
                $counter++;
            }
            foreach ($jobs as $id) {
                $cnt = count(array_filter($jobs, function ($a) use ($id) {
                    return $a == $id;
                }));
                if ($cnt == $counter) {
                    array_push($result, $id);
                }
            }
            $result = array_unique($result);



            $results = [];
            foreach ($result as $job)
            {
                $newJob = Job::find($job);
                array_push($results,$newJob);
            }


            $results = collect($results)->map(function($job)
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
                        "country" => $job->country,
                        "city" => $job->city,
                        "experience_min" => $job->experience_min,
                        "experience_max" => $job->experience_max,
                        "job_type" => $job->job_type->name,
                        "carer_level" => $job->carer_level->name,
                        "image" => $job->company->image,
                        "company_name" => $job->company->company_name,
                        "job_description" => $job->job_description->name,
                        "skill_names" => $skillNames,
                    ];

            });


            return $this->returnData(['response'], [$results],'Categories Data');
        }catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function uploadImagesTest(Request $request)
    {
        $images = $this->uploadImages($request);
        return 'done';
    }

    public function uploadImages(Request $request)
    {
        if ($request->hasFile('image')) {

            $files = $request->file('image');
            foreach ($files as $file) {

                $fileextension = $file->getClientOriginalExtension();

                $rand = rand();
                $filename = $file->getClientOriginalName();
                $file_to_store = time() . '_' . explode('.', $filename)[0].$rand . '_.' . $fileextension;

                $test = $file->move(public_path('assets/cvs'), $file_to_store);
                if ($test) {
                    $images [] = $file_to_store;
                }
            }
            $images = implode('|', $images);
            return $images;
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

    public function acceptForm(Request $request,$id)
    {
        try {
            $company = auth('company')->userOrFail();

            $applicant = Applicant::find($id);
            if(!$applicant || $applicant->job->company->id != $company->id)
            {
                return $this->returnError(201, 'Not a job');
            }
            if($applicant->status != '1')
            {
                return $this->returnError(201, 'already assigned');
            }
            $applicant->status = '2';
            $applicant->save();


            $newNotification = new Notification;
            $newNotification->user_type = 'person';
            $newNotification->user_id = $applicant->person->id;
            $newNotification->content_type = 'job';
            $newNotification->content_id = $applicant->job->id;
            $newNotification->seen = 0;
            $newNotification->notification = $applicant->job->company->company_name.' has accepted your application';
            $newNotification->save();

            (new MessageController())->NotifyApi(
                $applicant->job->company->device_token,
                "Accept Application",
                $applicant->job->company->company_name.' has accepted your application'
            );

            return $this->returnSuccessMessage('Added Successfully',200);
        }catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function declineForm(Request $request,$id)
    {
        try {
            $company = auth('company')->userOrFail();

            $applicant = Applicant::find($id);
            if(!$applicant || $applicant->job->company->id != $company->id)
            {
                return $this->returnError(201, 'Not a job');
            }
            if($applicant->status != '1')
            {
                return $this->returnError(201, 'already assigned');
            }
            $applicant->status = '3';
            $applicant->save();


            $newNotification = new Notification;
            $newNotification->user_type = 'person';
            $newNotification->user_id = $applicant->person->id;
            $newNotification->content_type = 'job';
            $newNotification->content_id = $applicant->job->id;
            $newNotification->seen = 0;
            $newNotification->notification = $applicant->job->company->company_name.' has decline your application';
            $newNotification->save();

            (new MessageController())->NotifyApi(
                $applicant->job->company->device_token,
                "Decline Application",
                $applicant->job->company->company_name.' has decline your application'
            );

            return $this->returnSuccessMessage('Added Successfully',200);
        }catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function addToShortList(Request $request,$job_id)
    {
        try {
            $company = auth('company')->userOrFail();

            $validator = Validator::make($request->all(), [
                'applicant_ids'=> 'required|string',
            ]);
            if ($validator->fails()) {
                return $this->returnValidationError(422, $validator);
            }
            $job = Job::find($job_id);


            if(!$job || $job->company->id != $company->id)
            {
                return $this->returnError(201, 'Not a job');
            }


            $applicant_idsArray = json_decode($request->applicant_ids);

            foreach ($applicant_idsArray as $applicant_id)
            {
                $applicant = Applicant::find($applicant_id);

                if(!$applicant || $applicant->job->company->id != $company->id)
                {
                    return $this->returnError(201, 'Not a applicant');
                }
                if($applicant->job->id != $job->id)
                {
                    return $this->returnError(201, 'this applicant is not belong to this job');
                }

                if($applicant->status != '2')
                {
                    return $this->returnError(201, 'not in considerate applicant');
                }

                $oldShortlist = Short_List::where('job_id',$job->id)->where('applicant_id',$applicant->id)->first();

                if ($oldShortlist)
                {
                    return $this->returnError(201, 'already added');
                }
            }



            foreach ($applicant_idsArray as $applicant_id)
            {
                $applicant = Applicant::find($applicant_id);
                $applicant->status = '4';
                $applicant->save();
                $newShortList = new Short_List();
                $newShortList->job_id = $job->id;
                $newShortList->applicant_id = $applicant->id;
                $newShortList->save();



                $newNotification = new Notification;
                $newNotification->user_type = 'person';
                $newNotification->user_id = $applicant->person->id;
                $newNotification->content_type = 'job';
                $newNotification->content_id = $applicant->job->id;
                $newNotification->seen = 0;
                $newNotification->notification = $applicant->job->company->company_name.' makes an appointment for interview';
                $newNotification->save();

                (new MessageController())->NotifyApi(
                    $applicant->job->company->device_token,
                    "Interview Appointment",
                    $applicant->job->company->company_name.' makes an appointment for interview'
                );

            }


            return $this->returnSuccessMessage('Added Successfully',200);
        }catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function acceptInterview(Request $request,$id)
    {
        try {
            $company = auth('company')->userOrFail();

            $applicant = Applicant::find($id);
            if(!$applicant || $applicant->job->company->id != $company->id)
            {
                return $this->returnError(201, 'Not a job');
            }
            if($applicant->status != '4')
            {
                return $this->returnError(201, 'already assigned');
            }
            $applicant->status = '5';
            $applicant->save();


            $newNotification = new Notification;
            $newNotification->user_type = 'person';
            $newNotification->user_id = $applicant->person->id;
            $newNotification->content_type = 'job';
            $newNotification->content_id = $applicant->job->id;
            $newNotification->seen = 0;
            $newNotification->notification = $applicant->job->company->company_name.' has accepted you in interview';
            $newNotification->save();

            (new MessageController())->NotifyApi(
                $applicant->job->company->device_token,
                "Accept Interview",
                $applicant->job->company->company_name.' has accepted you in interview'
            );

            return $this->returnSuccessMessage('Added Successfully',200);
        }catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function rejectionInterview(Request $request,$id)
    {
        try {
            $company = auth('company')->userOrFail();

            $applicant = Applicant::find($id);
            if(!$applicant || $applicant->job->company->id != $company->id)
            {
                return $this->returnError(201, 'Not a job');
            }
            if($applicant->status != '4')
            {
                return $this->returnError(201, 'already assigned');
            }
            $applicant->status = '6';
            $applicant->save();


            $newNotification = new Notification;
            $newNotification->user_type = 'person';
            $newNotification->user_id = $applicant->person->id;
            $newNotification->content_type = 'job';
            $newNotification->content_id = $applicant->job->id;
            $newNotification->seen = 0;
            $newNotification->notification = $applicant->job->company->company_name.' has rejected you in interview';
            $newNotification->save();

            (new MessageController())->NotifyApi(
                $applicant->job->company->device_token,
                "Rejection Interview",
                $applicant->job->company->company_name.' has rejected you in interview'
            );

            return $this->returnSuccessMessage('Added Successfully',200);
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
