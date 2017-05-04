<?php 
/**
 * MyYiiChatHandler (YiiChat A Software Interface for YiiChat Source Providers)
 *	a demostration class to serve as a source for the yiichat.
 *
 *	this class is invoked because you specify it in your YiiChatWidget 
 *	arguments passed to the widget.
 *
 *	this is the object fields required: (as an indexed array)
 *
 *		'id'				the post unique id
 *		'text'				the post text
 *		'time'				the time stamp
 *		'owner'				the name of the person who make this post
 *		'post_identity'		the ID of the person who make this post
 *
 *	the both methods in this handler receive:
 *
 *		$chat_id			the id provided in the widget, to discrimine 
 *							between various chats.
 *
 *		$identity			the identity (ID) of the person who is in chat
 *							(the post_identity field is the same as $identity
 *							only when we are creating a post: yiichat_post
 *
 *		$data				a user-defined value passed from the widget
 *
 * @uses CWidget
 * @version 1.0 
 * @author Christian Salazar <christiansalazarh@gmail.com> 
 * @license FREE BSD
 */
class MyYiiChatHandler extends CComponent implements IYiiChat {
	/**
	 	post a message into your database.
	 */
	public function yiichat_post($chat_id, $identity, $message, $data){
		return array(
			'id'=>time()+rand(10000,99999), 
			'text'=>$message, 
			'time'=>Yii::app()->format->formatDateTime(time()),
			'owner'=>'My Own Post',
			'post_identity'=>$identity,
		);
	}
	/**
	 	retrieve posts from your database, considering the last_id argument:
		$last_id is the ID of the last post sent by the other person:
			when -1: 
				you must reetrive all posts this scenario occurs when 
				the chat initialize, retriving your posts and those posted
				by the others.
			when >0: 
				you must retrive thoses posts that match this criteria:
					a) having an owner distinct as $identity
					b) having an ID greater than $last_id
				this scenario occurs when the post widget refreshs using
				a timer, in order to receive the new posts since last_id.
	 */
	public function yiichat_list_posts($chat_id, $identity, $last_id, $data){
		$ar = array();

		// simulation: other posts since last_id, because is a sym you dont see
		// any validation here.
		if($last_id != null)
		for($i=0;$i<=rand(0,4);$i++){
			$ar[] = array(
				'id'=>time()+$i, 
				'text'=>"random response ".rand(1000,999), 
				'time'=>Yii::app()->format->formatDateTime(time()),
				'owner'=>'Some Person',
				'post_identity'=>rand(1000,9999),
			);
		}

		// simulation: all posts.
		if($last_id == -1)
		for($i=0;$i<=rand(0,5);$i++){
			$myown = rand(0,1); // some posts comming from another people.
			$name = 'My Self';
			$post_identity = $identity;
			if($myown == 0){
				$post_identity = rand(1000,9999);
				$name = 'Another person #'.$post_identity;
			}
			$ar[] = array(
				'id'=>time()+$i, 
				'text'=>"test message sent from server rand: ".rand(1000,999), 
				'time'=>Yii::app()->format->formatDateTime(time()),
				'owner'=>$name,
				'post_identity'=>$post_identity,
			);
		}
		return $ar;
	}
}
?>
