<?php

class cl_ultramsg
{
        var $URL = "https://api.ultramsg.com/";
        // var $instance = "instance90285";
        // var $token = "8291rhvd0saedj7r";
        var $instance = "instance89505";
        var $token = "euz272ib029lfh5f";
        var $headers = [];
		
		public function __construct()
		{
            $this->URL = $this->URL.$this->instance;
            $this->headers[] = 'content-type: application/x-www-form-urlencoded';
		}
		
		public function setInstance($instance, $token){
		    $this->instance = $instance;
		    $this->token = $token;
		    $this->URL = "https://api.ultramsg.com/".$instance;
		}
		
		/*ENVIAR MENSAJES*/
		public function sendMessage($phone, $text, $priority=0)
        {
            $params = [
                'token' => $this->token,
                'to' => $phone,
                'body' => $text,
                'priority' => $priority
            ];
            $ch = curl_init($this->URL.'/messages/chat');
            
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));  
            curl_setopt($ch, CURLOPT_HTTPHEADER,$this->headers);  
            $response = curl_exec($ch);
            curl_close($ch);
            return json_decode($response, true);
        }
        
		public function sendOTP($phone, $code, $priority=0)
        {
            $params = [
                'token' => $this->token,
                'to' => $phone,
                'body' => 'Bienvenido a '.name_site.', Tu código de acceso es: '.$code,
                'priority' => $priority
            ];
            $ch = curl_init($this->URL.'/messages/chat');
            
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));  
            curl_setopt($ch, CURLOPT_HTTPHEADER,$this->headers);  
            $response = curl_exec($ch);
            curl_close($ch);
            return json_decode($response, true);
        }

        public function sendVideo($phone, $url, $text="", $priority=0)
        {
            $params = [
                'token' => $this->token,
                'to' => $phone,
                'video' => $url,
                'caption' => $text,
                // 'priority' => $priority
            ];
            $ch = curl_init($this->URL.'/messages/video');
            
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));  
            curl_setopt($ch, CURLOPT_HTTPHEADER,$this->headers);  
            $response = curl_exec($ch);
            curl_close($ch);
            return json_decode($response, true);
        }
}
?>