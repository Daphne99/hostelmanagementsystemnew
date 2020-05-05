<?php

namespace App\Jobs;

use Exception;
use App\Jobs\Job;
use App\Models2\messages_list;
use App\Models2\messages;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class convertImportMessages extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels, Queueable;

    public function __construct()
    {
        //
    }

    public function like_match($pattern, $subject)
    {
        $pattern = str_replace('%', '.*', preg_quote($pattern, '/'));
        return (bool) preg_match("/^{$pattern}$/i", $subject);
    }

    public function handle()
    {
        $msgs = messages_list::get()->toArray();
        $list = [];
        foreach( $msgs as $message )
        {
            $id = $message['id'];
            $user = $message['userId'];
            $usersng = (string)$user;
            $recipient = $message['toId'];
            $lastMsg = $message['lastMessage'];
            $lastMsgDate = $message['lastMessageDate'];
            $messageStatus = $message['messageStatus'];
            $enable_reply = $message['enable_reply'];
            $matStr = substr($lastMsg, 0 ,15);
            $last_Msg = (int)substr($lastMsgDate, 0 , 8);
            $mat = $matStr . '_' . $last_Msg;
            $mat_1 = $matStr . '_' . ( $last_Msg + 1 );
            $mat_2 = $matStr . '_' . ( $last_Msg - 1 );
            if( array_key_exists( $usersng, $list ) && count( $list[$usersng] ) )
            {
                if( array_key_exists($mat, $list[$usersng]) )
                {
                    foreach( $list[$usersng][$mat]['matchs'] as $match )
                    {
                        if( $this->like_match("%$mat_1%", $match) || $this->like_match("%$mat_2%", $match) )
                        {
                            if( !in_array( "$id", $list[$usersng][$mat]['ids'] ) ) $list[$usersng][$mat]['ids'][] = "$id";
                            if( !in_array( "$recipient", $list[$usersng][$mat]['recipients'] ) ) $list[$usersng][$mat]['recipients'][] = "$recipient";
                        }
                        else
                        {
                            if( !in_array( $mat_1, $list[$usersng][$mat]['matchs'] ) ) $list[$usersng][$mat]['matchs'][] = $mat_1;
                            if( !in_array( $mat_2, $list[$usersng][$mat]['matchs'] ) ) $list[$usersng][$mat]['matchs'][] = $mat_2;
                        }
                    }
                }
                else
                {
                    $list[$usersng][$mat] = [
                        'ids' => [ "$id" ],
                        'sender_id' => $user,
                        'recipients' => [ "$recipient" ],
                        'matchs' => [$mat_1, $mat_2],
                        'message' => $lastMsg,
                        'date' => $lastMsgDate,
                        'status' => $messageStatus,
                        'reply' => $enable_reply
                    ];
                }
            }
            else
            {
                $list[$usersng][$mat] = [
                    'ids' => [ "$id" ],
                    'sender_id' => $user,
                    'recipients' => [ "$recipient" ],
                    'matchs' => [$mat_1, $mat_2],
                    'message' => $lastMsg,
                    'date' => $lastMsgDate,
                    'status' => $messageStatus,
                    'reply' => $enable_reply
                ];
            }
        }
        $dbStoreList = [];
        foreach( $list as $userId => $matString )
        {
            foreach( $matString as $data )
            {
                if( count($data['ids']) == 1 )
                {
                    $dbStoreList[$userId][] = [
                        'userId' => $userId,
                        'sender' => $data['sender_id'],
                        'messagesIds' => [$data['ids'][0]],
                        'recipients' => [ $data['recipients'][0] ],
                        'lastMessage' => $data['message'],
                        'lastMessageDate' => $data['date'],
                        'status' => $data['status'],
                        'reply' => $data['reply']
                    ];
                }
                elseif( count($data['ids']) > 1 )
                {
                    $dbStoreList[$userId][] = [
                        'userId' => $userId,
                        'sender' => $data['sender_id'],
                        'messagesIds' => $data['ids'],
                        'recipients' => $data['recipients'],
                        'lastMessage' => $data['message'],
                        'lastMessageDate' => $data['date'],
                        'status' => $data['status'],
                        'reply' => $data['reply']
                    ];
                    // dd( $message );
                } else continue;
            }
        }
        $finalList = [];
        foreach( $dbStoreList as $user_sender_id => $innder )
        {
            foreach( $innder as $data )
            {
                $finalList[] = [
                    'userId' => $data['sender'],
                    'recipients_ids' => json_encode( $data['recipients'] ),
                    'lastMessage' => $data['lastMessage'],
                    'lastMessageDate' => $data['lastMessageDate'],
                    'messageStatus' => count( $data['recipients'] ) == 1 ? $data['status'] : 1,
                    'enable_reply' => count( $data['recipients'] ) == 1 ? $data['reply'] : 0,
                    'messages_ids' => json_encode( $data['messagesIds'] )
                ];
            }
        }
        
        DB::table('messages_list_grouped')->insert($finalList);
        $messages = DB::table('messages_list_grouped')->where('recipients_ids', 'LIKE', '%","%')->get();
        foreach( $messages as $messageOne )
        {
            $messages_ids = json_decode( $messageOne->messages_ids , true );
            if( json_last_error() != JSON_ERROR_NONE ) { $messages_ids = []; }
            $threadId = $messageOne->id;
            messages::where('is_grouped', 'no')->where('threadId', '0')->whereIn('messageId', $messages_ids)->update([ 'threadId' => $threadId, 'is_grouped' => 'yes' ]);
        }

    }

    public function failed(Exception $exception)
    {
        \Log::error($exception->getMessage());
    }
}