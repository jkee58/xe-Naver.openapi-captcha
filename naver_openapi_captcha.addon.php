<?php
  
// ini_set('display_errors', 1);
// ini_set('error_reporting', E_ALL & ~E_DEPRECATED & ~E_STRICT & ~E_NOTICE);

    if(!defined("__XE__")) exit();

    if(!class_exists('NaverCaptcha', false)) 
    {
        class NaverCaptcha
        {
            const CAPTCHA_AUTHED = 'naver_captcha_authed';
         
            var $target_acts = NULL;
            var $url;
            var $is_post = false;
            var $headers = array();

            private $addon_info;
            private $key;
            private $addon_path;
            private $html;
            private $skin;

            private $CLIENT_ID;
            private $CLIENT_SECRET;

            function setInfo(&$addon_info)
            {
                $this->addon_info = $addon_info;
                $this->CLIENT_ID =  $this->addon_info->client_id;
                $this->CLIENT_SECRET = $this->addon_info->client_secret;
                $this->skin = $this->addon_info->skin ?: 'default';
            }

            function setHeaders()
            {
                $this->headers[] = "X-Naver-Client-Id: ".$this->CLIENT_ID;
                $this->headers[] = "X-Naver-Client-Secret: ".$this->CLIENT_SECRET;
            }
           
            public function setPath($addon_path) { $this->addon_path = $addon_path; }
          
            function loadHtml() 
            {
                if (!$this->html)
                    $this->html = TemplateHandler::getInstance()->compile($this->addon_path . '/skin//' . $this->skin, 'view');
        
                return $this->html; 
            }
      
            function before_module_init(&$ModuleHandler)
            {
                $logged_info = Context::get('logged_info');
                if($logged_info->is_admin == 'Y' || $logged_info->is_site_admin)
                {
                    return false;
                }

                if($this->addon_info->target != 'all' && Context::get('is_logged'))
                {
                    return false;
                }

                if($_SESSION['XE_VALIDATOR_ERROR'] == -1)
                {
                    $_SESSION[self::CAPTCHA_AUTHED] = false;
                }
                if($_SESSION[self::CAPTCHA_AUTHED])
                {
                    return false;
                }
               
                $type = Context::get('captchaType');

                $this->target_acts = array('procBoardInsertDocument', 'procBoardInsertComment', 'procIssuetrackerInsertIssue', 'procIssuetrackerInsertHistory', 'procTextyleInsertComment');
                
                if(Context::get('captcha_action') != 'captchaImage'){
                    if(Context::getRequestMethod() != 'XMLRPC' && Context::getRequestMethod() !== 'JSON')
                    {
                    
                        
                        if($type == 'image')
                        {   
                            $rst = $this->compareCaptcha();
                            
                            if(is_array($rst)) {
                                $isEqual = $rst['result'];
                            } else {
                                $isEqual = $rst;
                            }

                            if($isEqual)
                            {
                                Context::loadLang(_XE_PATH_ . 'addons/naver_openapi_captcha/lang');
                                $_SESSION['XE_VALIDATOR_ERROR'] = -1;
                                $_SESSION['XE_VALIDATOR_MESSAGE'] = Context::getLang('captcha_denied');
                                $_SESSION['XE_VALIDATOR_MESSAGE_TYPE'] = 'error';
                                $_SESSION['XE_VALIDATOR_RETURN_URL'] = Context::get('error_return_url');
                                $ModuleHandler->_setInputValueToSession();
                            }
                        }
                        else
                        {
                            Context::addHtmlHeader('<script>
                                if(!captchaTargetAct) {var captchaTargetAct = [];}
                                captchaTargetAct.push("' . implode('","', $this->target_acts) . '");
                                </script>');

                            Context::loadFile(array('./addons/naver_openapi_captcha/skin/' . $this->skin . '/view.css', '', '', null), true);
                            Context::loadFile(array('./addons/naver_openapi_captcha/naver_imgCaptcha.js', 'body', '', null), true);
                        }
                    }
                }

                // compare session when calling actions such as writing a post or a comment on the board/issue tracker module
                if(in_array(Context::get('act'), $this->target_acts) && !$_SESSION[self::CAPTCHA_AUTHED])
                {
                    Context::loadLang(_XE_PATH_ . 'addons/naver_openapi_captcha/lang');
                    $ModuleHandler->error = "captcha_denied";
                }
                return true;
            }

              

            function before_module_proc()
            {                
                if($this->addon_info->act_type == 'everytime' && $_SESSION[self::CAPTCHA_AUTHED])
                {
                    unset($_SESSION[self::CAPTCHA_AUTHED]);
                }
            }

            function after_module_proc($moduleObject) { }

            function before_module_init_getHtml() {
                if ($_SESSION[self::CAPTCHA_AUTHED]) {
                    return false;
                }
        
                $rst = $this->getCaptchaKey();

                if ($rst['errorCode']) {
                    $err = $rst['errorCode'];
                    $message = $rst['errorMessage'];
                } else {
                    $err = 0;
                    $message = 'success';
                }

                printf(file_get_contents($this->addon_path . '/tpl/response.view.xml'), $err, $message, $this->loadHtml(), $_SESSION['captcha_keyword']);

                Context::close();               
                exit();
            }

            function before_module_init_captchaImage()
            {
                if($_SESSION[self::CAPTCHA_AUTHED])
                {
                    return false;
                }

                $keyword = $_SESSION['captcha_keyword'];
                
                $im = $this->getCaptchaImage($keyword);
    
                echo $im;    
                Context::close();
                exit();
            }

            function before_module_init_captchaCompare()
            {                        

                $rst = $this->compareCaptcha($_SESSION['captcha_keyword'], Context:: get('captcha_value'));
                
                if (is_array($rst)) {

                    if ($rst['errorCode']) {
                        $err = $rst['errorCode'];
                        $message = $rst['errorMessage'];
                        $result = false;
                    } else {
                        $err = 0;
                        if ($rst['result']) {
                            $message = 'success';
                        } else {
                            $message = Context::getLang('captcha_denied');
                        }
                        
                        $result = $rst['result'];
                        
                    }

                    printf(file_get_contents($this->addon_path . '/tpl/response.xml'), $err, $message, $result);
                   
				 

                } else {
                    if ($rst) {
                        printf(file_get_contents($this->addon_path . '/tpl/response.xml'), 0, 'success', true);
                    } else {
                        printf(file_get_contents($this->addon_path . '/tpl/response.xml'), 1, 'Server Error', false);
                    }
                }
                   
                Context::close();
                exit();
            }

            function curlInit($url) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, $this->is_post);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
                curl_setopt($ch, CURLOPT_REFERER, $url);
                
                return $ch;
            }

            function getCaptchaKey() {
                $code = "0";
                $url = "https://openapi.naver.com/v1/captcha/nkey?code=".$code;
                $ch = $this->curlInit($url);
                $response = curl_exec ($ch);
                $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close ($ch);                

                if($status_code == 200) {
                    $_SESSION['captcha_keyword'] = json_decode($response, true)['key'];
                } 

                return  json_decode($response, true);
            }

            function getCaptchaImage() {
               
                $flag = 1;
                while ($flag) {
                    $key = $_SESSION['captcha_keyword'];
                    $url = "https://openapi.naver.com/v1/captcha/ncaptcha.bin?key=".$key;
                    $ch = $this->curlInit($url);
                    $response = curl_exec ($ch);
                    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close ($ch);


                    if($status_code == 200) {
                        $flag = 0;
                        return $response;
                    } else {                      
                        $errResponse = json_decode($response, true);                                 
                        if ($errResponse['errorCode'] == 'CT001') {
                            $this->getCaptchaKey();
                        } else {
                            $flag = 0;
                        }
                    } 
                }               
            }

            function compareCaptcha() {     
                $key = $_SESSION['captcha_keyword'];
                $value = Context:: get('captcha_value');

                if(!in_array(Context::get('act'), $this->target_acts)) return true;

                if($_SESSION[self::CAPTCHA_AUTHED])
                {
                    return true;
                }

                $code = "1";
                $url = "https://openapi.naver.com/v1/captcha/nkey?code=".$code."&key=".$key."&value=".$value;
                $ch = $this->curlInit($url);
                $response = curl_exec ($ch);
                $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                               
                curl_close ($ch);

                if($status_code == 200) 
                {           
                    $result = json_decode($response, true)['result'];
                    if ($result)
                    {
                        $_SESSION[self::CAPTCHA_AUTHED] = true;
                    } else {
                        unset($_SESSION[self::CAPTCHA_AUTHED]);
                    }
                    
                    
                } else {
                    $errResponse = json_decode($response, true);                                 
                    if ($errResponse['errorCode'] == 'CT001' || $errResponse['errorCode'] == 'CT002' ) {
                        $this->getCaptchaKey();
                    }
                }

                return json_decode($response, true);
            }
        }

        
            $GLOBALS['__NaverCaptcha__'] = new NaverCaptcha;
            $GLOBALS['__NaverCaptcha__']->setInfo($addon_info);
            $GLOBALS['__NaverCaptcha__']->setHeaders();
            $GLOBALS['__NaverCaptcha__']->setPath(_XE_PATH_.'addons/naver_openapi_captcha');
            Context::set('oNaverCaptcha', $GLOBALS['__NaverCaptcha__']);
        
    }

    $oAddonNaverCaptcha = &$GLOBALS['__NaverCaptcha__'];
   
    if(method_exists($oAddonNaverCaptcha, $called_position))
    {
        if(!call_user_func_array(array(&$oAddonNaverCaptcha, $called_position), array(&$this)))
        {
            return false;
        }       
        
    }

    $addon_act = Context::get('captcha_action');
    if($addon_act && method_exists($oAddonNaverCaptcha, $called_position . '_' . $addon_act))
    {
        if(!call_user_func_array(array(&$oAddonNaverCaptcha, $called_position . '_' . $addon_act), array(&$this)))
        {
            return false;
        }
    }

/* End of file naver_openapi_captcha.addon.php */
/* Location: ./addons/naver_openapi_captcha/naver_openapi_captcha.addon.php */
