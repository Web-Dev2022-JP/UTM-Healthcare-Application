<?php

namespace App\Http\Controllers;

use Notification;
use App\Models\User;

use Brick\Math\BigInteger;
use Illuminate\Http\Request;

use App\Models\BookSchedules;
use App\Http\Requests\BookedRequest;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ConsultationRequest;

use App\Notifications\SendEmailNotification;

use App\Http\Resources\BookScheduleResources;
use App\Http\Resources\CheckBookStatusResource;

class BookSchedulesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return BookScheduleResources::collection(  
            BookSchedules::where('schedule_id', Auth::user()->id)
            ->join('Users','Users.id', '=', 'book_schedules.user_id')
            ->where('status', 'pending')
            ->get());//get all where login user = user_id
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ConsultationRequest $request)
    {
        $request->validated($request->all());
        $checkedBooked = BookSchedules::where('schedule_id',$request->schedule_id)->where('status','booked')->first();
        if($checkedBooked){
           $bookSchedules = ['message'=>'already booked'];
           return $bookSchedules;
        }else{
            $checkedUserPending = BookSchedules::where('user_id',Auth::user()->id)->where('status','pending')->first();
            if($checkedUserPending != ''){
                $bookSchedules = ['message'=>'You already sent a request!'];
                return $bookSchedules;
            }else{
                $bookSchedules = BookSchedules::create([
                    'user_id' => Auth::user()->id,
                    'schedule_id' => $request->schedule_id,
                    'details' => $request->details,
                    'status' => $request->status,
                    'date' => $request->date,
                    'time' => $request->time,
    
                ]);
                return new BookScheduleResources($bookSchedules);
            }
        }
        // return $bookSchedules;
        // dd($bookSchedules);
        // return new BookScheduleResources($bookSchedules);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\BookSchedules  $bookSchedules
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request,$id)
    {
        return CheckBookStatusResource::collection(
            BookSchedules::where('user_id', $id)->get()
        );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\BookSchedules  $bookSchedules
     * @return \Illuminate\Http\Response
     */
    public function edit(BookSchedules $bookSchedules)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\BookSchedules  $bookSchedules
     * @return \Illuminate\Http\Response
     */
    public function update(BookedRequest $request)
    {
        $updateSched = BookSchedules::where('user_id',$request->student_id)->where('status','pending')->first();
        // if($request->from == 'student' && $updateSched == ''){
        //     $updateSched->date = $request->date;
        //     $updateSched->time = $request->time;
        // }else{
        //     $updateSched->status = $request->status;
        //     $updateSched->details = $request->details;
        // } 
        // $updateSched->save();
        if($updateSched == ''){
            $updateSched = ['message'=>'No records.'];
        }else{
             if($request->from == 'student'){
                $updateSched->date = $request->date;
                $updateSched->time = $request->time;
            }else{
                $updateSched->status = $request->status;
                $updateSched->details = $request->details;
            } 
            $updateSched->save();
        }
        return $updateSched;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\BookSchedules  $bookSchedules
     * @return \Illuminate\Http\Response
     */
    public function destroy(BookSchedules $bookSchedules,$id)
    {
        $deleteRequest = BookSchedules::where('user_id',$id)->where('status','pending')->first();
        $deleteRequest->delete();
        return $deleteRequest;
    }

     /**
     * check status.
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\BookSchedules  $bookSchedules
     * @return \Illuminate\Http\Response
     */
    public function checkStatus(BookSchedules $bookSchedules,$id){
        $checkStat = BookSchedules::where('user_id',$id)->first();
        return $checkStat;
    }

    /**
     * sendNotif one doctor.
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\BookSchedules  $bookSchedules
     * @return \Illuminate\Http\Response
     */
    public function sendnotification($id){
        $user = User::where('id',$id)->get();
        $details = [
            'greeting'=>'Hi email notification test',
            'body'=>'This is the email body',
            'actiontext'=>'Hi im a developer',
            'actionurl'=>'/',
            'lastline'=>'Thank you user.',
            // 'useremail'=>
        ];

        Notification::send($user, new SendEmailNotification($details));
        return $user;
    } 
}
