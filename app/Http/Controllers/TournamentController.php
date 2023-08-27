<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\Notification;
use App\Models\Person;
use App\Models\Tournament;
use App\Models\Tournament_Competitor;
use App\Models\Tournament_Competitor_Answer;
use App\Models\Tournament_Question;
use App\Models\Tournament_Question_Choose;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TournamentController extends Controller
{

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'job_description_id'=>'required|exists:job_descriptions,id',
            'country' => 'required|string',
            'city' => 'required|string',
            'experience_min' => 'required|numeric',
            'experience_max' => 'required|numeric',
            'question_titles'=> 'required|string',
            'answers'=> 'required|string',
            'chooses'=> 'required|string',

        ]);
        if ($validator->fails()) {
            return $this->returnValidationError(422, $validator);
        }

        try {
            $admin = auth('admin')->userOrFail();



            $questionsArray = json_decode($request->question_titles);

            $answersArray = json_decode($request->answers);

            $choosesArray = json_decode($request->chooses);

            if(count($questionsArray) != count($answersArray))
            {
                return $this->returnError(201, 'Questions must be equal to Answers array');
            }


            if(count($answersArray) != count($choosesArray))
            {
                return $this->returnError(201, 'chooses array must be Answers types');
            }


            $newTournament = new Tournament();

            $newTournament->job_description_id = $request->job_description_id;
            $newTournament->country = $request->country;
            $newTournament->city = $request->city;
            $newTournament->experience_min = $request->experience_min;
            $newTournament->experience_max = $request->experience_max;
            $newTournament->save();



            for ($i = 0 ; $i < count($questionsArray) ; $i++)
            {
                $newTournament_Question = new Tournament_Question();
                $newTournament_Question->tournament_id = $newTournament->id;
                $newTournament_Question->title = $questionsArray[$i];
                $newTournament_Question->right_answer = $answersArray[$i];
                $newTournament_Question->save();

                $oneQuestionChoosesArray = $choosesArray[$i];
                foreach ($oneQuestionChoosesArray as $oneChoose)
                {
                    $newChoose = new Tournament_Question_Choose();
                    $newChoose->tournament_question_id = $newTournament_Question->id;
                    $newChoose->choose = $oneChoose;
                    $newChoose->save();
                }
            }

            $acceptedPersons = [];
            $persons = Person::where('country',$request->country)->where('city',$request->city)->get();
            foreach ($persons as $person)
            {
                if ($person->experience_year->experience_year >= $request->experience_min && $person->experience_year->experience_year <= $request->experience_max)
                {
                    foreach ($person->person_job_descriptions as $person_job_description)
                    {
                        if ($person_job_description->job_description->id == $request->job_description_id)
                        {
                            $acceptedPersons [] = $person->id;
                        }
                    }
                }

            }


            foreach ($acceptedPersons as $personID)
            {
                $person = Person::find($personID);

                $newNotification = new Notification;
                $newNotification->user_type = 'person';
                $newNotification->user_id = $person->id;
                $newNotification->content_type = 'tournament';
                $newNotification->content_id = $newTournament->id;
                $newNotification->seen = 0;
                $newNotification->notification = 'join our challenge';
                $newNotification->save();

                (new MessageController())->NotifyApi(
                    $person->device_token,
                    "Tournament Time",
                    'join our challenge'
                );
            }

            return $this->returnSuccessMessage('Added successfully',200);

        }catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

    }

    public function applyTournament(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tournament_id'=>'required|exists:tournaments,id',
            'questions_id'=> 'required|string',
            'answers'=> 'required|string',
            'timer'=> 'required|string',

        ]);
        if ($validator->fails()) {
            return $this->returnValidationError(422, $validator);
        }

        try {
            $person = auth('person')->userOrFail();


            $oldTournamentCompetitor  = Tournament_Competitor::where('tournament_id',$request->tournament_id)
                ->where('person_id',$person->id)
                ->first();

            if ($oldTournamentCompetitor)
            {
                return $this->returnError(201, 'Already Joined');
            }


            $questions_idArray = json_decode($request->questions_id);

            $answersArray = json_decode($request->answers);



            if(count($questions_idArray) != count($answersArray))
            {
                return $this->returnError(201, 'Questions must be equal to Answers array');
            }

            foreach ($questions_idArray as $item)
            {
                $Tournament_Question= Tournament_Question::find($item);
                if (!$Tournament_Question)
                {
                    return $this->returnError(201, 'Not Tournament_Question id');
                }
            }


            $newTournament_Competitor = new Tournament_Competitor();
            $newTournament_Competitor->tournament_id = $request->tournament_id;
            $newTournament_Competitor->person_id = $person->id;
            $newTournament_Competitor->rank = 0;
            $newTournament_Competitor->points = 0;
            $newTournament_Competitor->timer = $request->timer;
            $newTournament_Competitor->save();


            $points = 0;

            for ($i = 0 ; $i < count($answersArray) ; $i++)
            {
                $newTournament_Competitor_Answer = new Tournament_Competitor_Answer();
                $newTournament_Competitor_Answer->tournament_competitor_id = $newTournament_Competitor->id;
                $newTournament_Competitor_Answer->tournament_question_id = $questions_idArray[$i];
                $newTournament_Competitor_Answer->answer = $answersArray[$i];
                $newTournament_Competitor_Answer->save();

                $Tournament_Question= Tournament_Question::find($questions_idArray[$i]);

                if ($answersArray[$i] == $Tournament_Question->right_answer)
                {
                    $points++;
                }

            }

            $newTournament_Competitor->points = $points;
            $newTournament_Competitor->save();


            return $this->returnSuccessMessage('Applied successfully',200);

        }catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

    }

    public function getTournaments(Request $request)
    {
        try {
            $person = auth('person')->userOrFail();


            $acceptedTournaments = [];
            $tournaments = Tournament::all();

            foreach ($tournaments as $tournament)
            {
                if ($person->experience_year->experience_year >= $tournament->experience_min && $person->experience_year->experience_year <= $tournament->experience_max)
                {

                    foreach ($person->person_job_descriptions as $person_job_description)
                    {
                        if ($person_job_description->job_description->id == $tournament->job_description_id)
                        {
                            $oldTournamentCompetitor  = Tournament_Competitor::where('tournament_id',$tournament->id)
                                ->where('person_id',$person->id)
                                ->first();

                            if (!$oldTournamentCompetitor)
                            {
                                $acceptedTournaments [] = $tournament->id;
                            }

                        }
                    }
                }
            }



            $acceptedTournaments = collect($acceptedTournaments)->map(function($oneRecord)
            {
                $tournament = Tournament::find($oneRecord);

                $questions = [];
                foreach ($tournament->tournament_questions as $question)
                {
                    $chooses = [];
                    foreach ($question->tournament_question_chooses as $oneChoose)
                    {
                        $objectChoose =
                            [
                                'choose' => $oneChoose->choose
                            ];
                        $chooses [] = $objectChoose;
                    }
                    $object =
                        [
                            'question_id' => $question->id,
                            'title' => $question->title,
                            'chooses' => $chooses,
                        ];
                    $questions [] = $object;
                }

                return
                    [
                        "id" => $tournament->id,
                        "job_description" => $tournament->job_description->name,
                        "questions" => $questions,
                    ];
            });


            return $this->returnData(['response'], [$acceptedTournaments],'Tournaments Form');
        }catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function getTournamentsWeb(Request $request,$numOfPage,$numOfRows)
    {
        try {
            $person = auth('person')->userOrFail();


            $acceptedTournaments = [];
            $tournaments = Tournament::all();

            foreach ($tournaments as $tournament)
            {
                if ($person->experience_year->experience_year >= $tournament->experience_min && $person->experience_year->experience_year <= $tournament->experience_max)
                {

                    foreach ($person->person_job_descriptions as $person_job_description)
                    {
                        if ($person_job_description->job_description->id == $tournament->job_description_id)
                        {
                            $oldTournamentCompetitor  = Tournament_Competitor::where('tournament_id',$tournament->id)
                                ->where('person_id',$person->id)
                                ->first();

                            if (!$oldTournamentCompetitor)
                            {
                                $acceptedTournaments [] = $tournament->id;
                            }

                        }
                    }
                }
            }

            $allLength = count($acceptedTournaments);
            $acceptedSend = [];
            $skippedNumbers = ($numOfPage - 1) * $numOfRows;
            $counterSkip = 0;
            $counterSend = 0;
            foreach ($acceptedTournaments as $acceptedTournament)
            {
                if ($skippedNumbers == $counterSkip)
                {
                    if ($counterSend < $numOfRows)
                    {
                        $acceptedSend [] = $acceptedTournament;
                        $counterSend++;
                    }

                }else
                {
                    $counterSkip++;
                }
            }
            $acceptedTournaments = $acceptedSend;


            $acceptedTournaments = collect($acceptedTournaments)->map(function($oneRecord)
            {
                $tournament = Tournament::find($oneRecord);

                $questions = [];
                foreach ($tournament->tournament_questions as $question)
                {
                    $chooses = [];
                    foreach ($question->tournament_question_chooses as $oneChoose)
                    {
                        $objectChoose =
                            [
                                'choose' => $oneChoose->choose
                            ];
                        $chooses [] = $objectChoose;
                    }
                    $object =
                        [
                            'question_id' => $question->id,
                            'title' => $question->title,
                            'chooses' => $chooses,
                        ];
                    $questions [] = $object;
                }

                return
                    [
                        "id" => $tournament->id,
                        "job_description" => $tournament->job_description->name,
                        "questions" => $questions,
                    ];
            });

            $result =
                [
                    'acceptedTournaments' => $acceptedTournaments,
                    'length' => $allLength,
                ];

            return $this->returnData(['response'], [$result],'Tournaments Form');
        }catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function getCompetitors(Request $request,$job_id)
    {
        try {
            $company = auth('company')->userOrFail();

            $job = Job::find($job_id);
            if(!$job)
            {
                return $this->returnError(201, 'Not a job');
            }

            $tornament = Tournament::where('job_description_id',$job->job_description_id)
                ->where('country',$job->country)
                ->where('city',$job->city)
                ->first();


            if (!$tornament)
            {
                $empty = [];
                return $this->returnData(['response'], [$empty],'Tournaments Data');
            }

            $tournament_competitors = collect($tornament->tournament_competitors)->map(function($oneRecord) use ($tornament)
            {
                if($oneRecord->person->image)
                {
                    $oneRecord->person->image = asset('/assets/persons/' . $oneRecord->person->image );
                }
                $compatators = Tournament_Competitor::where('tournament_id',$tornament->id)->orderBy('points', 'DESC')->orderBy('timer', 'ASC')->get();

                $rank = 0;

                for ($i = 0 ; $i < count($compatators) ; $i++)
                {
                    if ($compatators[$i]->id == $oneRecord->id)
                    {
                        $rank = $i + 1;
                    }
                }

                return
                    [
                        "id" => $oneRecord->id,
                        "person_id" => $oneRecord->person->id,
                        "first_name" => $oneRecord->person->first_name,
                        "last_name" => $oneRecord->person->last_name,
                        "person_image" => $oneRecord->person->image,
                        "rank" => $rank,
                    ];
            });


            return $this->returnData(['response'], [$tournament_competitors],'Tournaments Data');
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
