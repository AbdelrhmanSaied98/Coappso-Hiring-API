<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TournamentController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {

    Route::post('register', [AuthController::class,'register']);
    Route::post('login', [AuthController::class,'login']);
    Route::post('logout', [AuthController::class,'logout']);
    Route::get('profile/{type}/{id}', [AuthController::class,'profile']);
    Route::get('getNewToken/{type}', [AuthController::class,'getNewToken']);
    Route::get('testAuth', [AuthController::class,'testAuth']);
    Route::post('forgetPassword', [AuthController::class,'forgetPassword']);
    Route::post('verifyCode', [AuthController::class,'verifyCode']);
    Route::post('updatePassword', [AuthController::class,'updatePassword']);

    Route::get('getNotification/{numOfPage}/{numOfRows}', [AuthController::class,'getNotification']);

    Route::get('getBaniAdam', [AuthController::class,'getBaniAdam']);


});


Route::group([
    'middleware' => ['api','checkCompanyMiddelware'],
    'prefix' => 'company'
], function ($router) {

    Route::post('jobs', [JobController::class,'store']);
    Route::get('home', [CompanyController::class,'home']);
    Route::get('jobs/{id}', [JobController::class,'show']);
    Route::post('uploadProfileImage', [AuthController::class,'uploadProfileImage']);
    Route::patch('update', [CompanyController::class,'update']);
    Route::post('jobs/{id}', [JobController::class,'update']);
    Route::get('form/jobs/{id}', [JobController::class,'getQuestions']);
    Route::post('addQuestion/jobs/{id}', [JobController::class,'addQuestion']);
    Route::delete('deleteQuestion/{id}', [JobController::class,'deleteQuestion']);
    Route::delete('delete/jobs/{id}', [JobController::class,'destroy']);
    Route::get('getApplicants/jobs/{id}', [CompanyController::class,'getApplicants']);
    Route::get('getApplicantsAnswer/applicants/{id}', [CompanyController::class,'getApplicantsAnswer']);
    Route::get('getCompanyNotification', [CompanyController::class,'getCompanyNotification']);
    Route::post('accept/applicants/{id}', [JobController::class,'acceptForm']);
    Route::post('decline/applicants/{id}', [JobController::class,'declineForm']);
    Route::get('getApplicants/accept/jobs/{id}', [CompanyController::class,'getAccepts']);
    Route::get('getApplicants/decline/jobs/{id}', [CompanyController::class,'getDeclines']);
    Route::get('dashboard', [CompanyController::class,'dashboard']);
    Route::post('addToShortList/jobs/{job_id}', [JobController::class,'addToShortList']);
    Route::get('getShortList/jobs/{job_id}', [CompanyController::class,'getShortList']);
    Route::post('acceptInterview/applicants/{id}', [JobController::class,'acceptInterview']);
    Route::post('rejectionInterview/applicants/{id}', [JobController::class,'rejectionInterview']);
    Route::get('getCompetitors/{id}', [TournamentController::class,'getCompetitors']);
});

Route::group([
    'middleware' => ['api','checkPersonMiddelware'],
    'prefix' => 'person'
], function ($router) {

    Route::get('home', [PersonController::class,'home']);
    Route::get('jobs/{id}', [JobController::class,'show_for_person']);
    Route::post('uploadProfileImage', [AuthController::class,'uploadProfileImage']);
    Route::post('update', [PersonController::class,'update']);
    Route::post('addEducation', [PersonController::class,'addEducation']);
    Route::delete('deleteEducation/{id}', [PersonController::class,'deleteEducation']);
    Route::post('addSkill', [PersonController::class,'addSkill']);
    Route::delete('deleteSkill/{id}', [PersonController::class,'deleteSkill']);
    Route::post('addLanguage', [PersonController::class,'addLanguage']);
    Route::delete('deleteLanguage/{id}', [PersonController::class,'deleteLanguage']);
    Route::post('addExperience', [PersonController::class,'addExperience']);
    Route::delete('deleteExperience/{id}', [PersonController::class,'deleteExperience']);
    Route::get('form/jobs/{id}', [JobController::class,'getForm']);
    Route::post('apply/jobs/{id}', [JobController::class,'applyJob']);
    Route::get('appliedJobs', [PersonController::class,'appliedJobs']);
    Route::get('getApplicantsAnswer/applicants/{id}', [PersonController::class,'getApplicantsAnswerForPerson']);
    Route::post('jobs/search/{title}', [JobController::class,'searchJob']);
    Route::post('jobs/save/{job_id}', [PersonController::class,'saveJob']);
    Route::delete('jobs/unsave/{job_id}', [PersonController::class,'unSaveJob']);
    Route::get('getSavedJobs', [PersonController::class,'getSavedJobs']);
    Route::get('getPersonNotification', [PersonController::class,'getPersonNotification']);
    Route::get('getTournaments', [TournamentController::class,'getTournaments']);
    Route::get('getTournamentsWeb/{numOfPage}/{numOfRows}', [TournamentController::class,'getTournamentsWeb']);
    Route::post('applyTournament', [TournamentController::class,'applyTournament']);

});


Route::group([
    'middleware' => 'api',
    'prefix' => 'admin'
], function ($router) {

    //Get

    Route::get('getPersons/{numOfPage}/{numOfRows}', [AdminController::class,'getPersons']);
    Route::get('getPersonsSearch/{name}/{numOfPage}/{numOfRows}', [AdminController::class,'getPersonsSearch']);
    Route::get('getPersonDetails/{id}', [AdminController::class,'getPersonDetails']);
    Route::get('getCompanies/{numOfPage}/{numOfRows}', [AdminController::class,'getCompanies']);
    Route::get('getCompaniesSearch/{name}/{numOfPage}/{numOfRows}', [AdminController::class,'getCompaniesSearch']);
    Route::get('getCompanyDetails/{id}', [AdminController::class,'getCompanyDetails']);
    Route::get('getJobs/{numOfPage}/{numOfRows}', [AdminController::class,'getJobs']);
    Route::get('getJobDetails/{id}', [AdminController::class,'getJobDetails']);
    Route::get('getApplicants/{numOfPage}/{numOfRows}', [AdminController::class,'getApplicants']);
    Route::get('getApplicantsAnswer/{id}', [AdminController::class,'getApplicantsAnswer']);

    //Update

    Route::post('updateCompany/{id}', [AdminController::class,'updateCompany']);
    Route::post('updatePerson/{id}', [AdminController::class,'updatePerson']);
    Route::post('changePassword', [AdminController::class,'changePassword']);


    //Delete

    Route::delete('delete/persons/{id}', [AdminController::class,'deletePerson']);
    Route::delete('delete/companies/{id}', [AdminController::class,'deleteCompany']);
    Route::delete('delete/jobs/{id}', [AdminController::class,'deleteJob']);
    Route::delete('delete/applicants/{id}', [AdminController::class,'deleteApplicant']);

    //Feature

    Route::post('block/persons/{id}', [AdminController::class,'blockPerson']);
    Route::post('block/companies/{id}', [AdminController::class,'blockCompany']);
    Route::post('unblock/persons/{id}', [AdminController::class,'unblockPerson']);
    Route::post('unblock/companies/{id}', [AdminController::class,'unblockCompany']);
    Route::post('ban/persons/{id}', [AdminController::class,'banPerson']);
    Route::post('ban/companies/{id}', [AdminController::class,'banCompany']);
    Route::post('unban/persons/{id}', [AdminController::class,'unbanPerson']);
    Route::post('unban/companies/{id}', [AdminController::class,'unbanCompany']);
    Route::post('createTournament', [TournamentController::class,'store']);


});







