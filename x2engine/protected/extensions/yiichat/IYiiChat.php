<?php 
/**
 * IYiiChat A Software Interface for YiiChat Source Providers
 *
 *
 * @uses CWidget
 * @version 1.0 
 * @author Christian Salazar <christiansalazarh@gmail.com> 
 * @license FREE BSD
 */
interface IYiiChat {
	public function yiichat_post($chat_id, $identity, $message, $data);
	public function yiichat_list_posts($chat_id, $identity, $last_id, $data);
}
?>
