<?php

    /****************************************************************************/
    /*                                                                          */
    /* desc:        class to interact with the s3 sms gateway over http. this   */
    /*              requires PHP4.3.x with mod_ssl compiled in if you intend to */
    /*              use https to communicate with the gateway                   */
    /*                                                                          */
    /* version:     1.2                                                         */
    /* author:      ramez zaki                                                  */
    /* copyright:   copyright (c) 2001-2004. all rights reserved                */
    /* date:        17/08/2004                                                  */
    /*                                                                          */
    /****************************************************************************/

    // server urls
    define("S3_SERVER_URL",                 "http://api.directsms.com.au");
    define("S3_SERVER_URL_SECURE",          "https://api.directsms.com.au");

    // uris for the various operations
    define("URI_CONNECT",                   "/s3/http/connect");
    define("URI_DISCONNECT",                "/s3/http/disconnect");
    define("URI_GET_BALANCE",               "/s3/http/get_balance");
    define("URI_GET_REPLIES",               "/s3/http/get_replies");
    define("URI_GET_INBOUND_MESSAGES",      "/s3/http/get_inbound_messages");
    define("URI_SEND_BRANDED_MESSAGE",      "/s3/http/send_branded_message");
    define("URI_SEND_TWO_WAY_MESSAGE",      "/s3/http/send_two_way_message");
    define("URI_SCHD_BRANDED_MESSAGE",      "/s3/http/schedule_branded_message");
    define("URI_SCHD_TWO_WAY_MESSAGE",      "/s3/http/schedule_two_way_message");

    // the various operation result prefixes (or is it prefixi)
    define("OP_CODE_ID",                    "id");
    define("OP_CODE_ERR",                   "err");
    define("OP_CODE_CREDITS",               "credits");
    define("OP_CODE_REPLIES",               "replies");

    // error messages
    define("ERROR_SMS_GATEWAY_UNREACHABLE", "sms gateway unreachable");
    define("ERROR_INVALID_RESPONSE",        "invalid gateway response");

    // the various field prefixes in a 2-way sms reply or an inbound sms
    define("PREFIX_MESSAGEID",              "id: ");
    define("PREFIX_INBOUND_NUMBER",         "inbound: ");
    define("PREFIX_MOBILE",                 "mobile: ");
    define("PREFIX_MESSAGE",                "message: ");
    define("PREFIX_WHEN",                   "when: ");

    // fixed lengths for 2-way sms replies
    define("LENGTH_MESSAGEID",              12);
    define("LENGTH_MESSAGE",                160);
    define("LENGTH_MOBILE",                 20);

    class sms_connection
    {
        /************************************************************************/
        /* public attributes                                                    */
        /************************************************************************/
        var $connectionid = null;       // connection id to use with all operations
                                        // with the server

        var $result       = null;       // positive result retrieved after last
                                        // operation

        var $error        = null;       // error message retrieved from server

        /************************************************************************/
        /* private attributes                                                   */
        /************************************************************************/
        var $server_url   = null;       // the base url to use when comminucating with the server,
                                        // there is a slight difference between the http and https
                                        // urls

        var $response     = null;       // the raw html response received from the server

        /************************************************************************/
        /* public methods                                                       */
        /************************************************************************/

        /**
         *  create a new sms connection
         *
         *  @param  $secure     communicate with the sms gateway over https, which is more secure
         *                      however, you will need mod_ssl built into your php instalation
         */
        function sms_connection($secure = false)
        {
            // set the server_url
            if($secure)
            {
                // use https
                $this->server_url = S3_SERVER_URL_SECURE;
            }
            else
            {
                // use normal http
                $this->server_url = S3_SERVER_URL;
            }

            // check if the server is contactable
            if(!@file($this->server_url . URI_CONNECT))
            {
                $this->error = ERROR_SMS_GATEWAY_UNREACHABLE;
            }
        }

        /**
         *  connect to the s3 sms gateway with the given
         *  credentials. the $licence_key is only used when
         *	distributing the software to other customers
         *
         *  @param  $username   	your directsms username
         *  @param  $password   	the corresponding password
         *  @param  $licence_key	a valid licence key for an
         *							enterprise licence (optional)
         *
         *  @return boolean
         */
        function connect($username, $password, $licence_key = null)
        {
            // generate the request string
	        $url = $this->server_url . URI_CONNECT . "?username=" . $username . "&password=" . $password;

	        // add the licence key
            if(!is_null($licence_key))
            {
	            $url .= "&licence_key=" . $licence_key;
            }

            // send the http request, this will fail and
            // return false if the server is not responding
            // or an error is found, i.e the server responded
            // with 'err: invalid login credentials'
            if($this->send_request($url))
            {
                // connection established, set the connection id
                // we will use it in subsequent operations
                $this->connectionid = $this->result;

                // success
                return true;
            }
            else
            {
                // error message was already set by send_request()
                return false;
            }
        }

        /**
         *  send a branded sms message through the s3 sms gateway
         *
         *  @param  $message    text message to send
         *  @param  mobiles     array of valid mobile phone numbers
         *  @param  $senderid   sender id to use
         *
         *  @return string      the message id returned fot this sms by
         *                      the s3 gateway
         */
        function send_branded_sms($message, $mobiles, $senderid)
        {
            // if we are not connected, stop
            if(!$this->is_connected())
            {
                return null;
            }

            // generate the request string
            $url =  $this->server_url . URI_SEND_BRANDED_MESSAGE . "?connectionid=" . $this->connectionid;
            $url .= "&message=" . urlencode($message) . "&senderid=" . urlencode(substr($senderid,0,8)) . "&to=" . $this->array_to_string($mobiles);

            // send the request off to the server
            if($this->send_request($url))
            {
                return $this->result;
            }
            else
            {
                return null;
            }
        }

        /**
         *  schedule a branded sms message through the s3 sms gateway
         *
         *  @param  $message    text message to send
         *  @param  mobiles     array of valid mobile phone numbers
         *  @param  $senderid   sender id to use
         *	@param	$time		date/time to send the message represented as the number
         *						of seconds since the UNIX epoch (January 1 1970 00:00:00 GMT)
         *
         *  @return string      the message id returned fot this sms by
         *                      the s3 gateway
         */
        function schedule_branded_sms($message, $mobiles, $senderid, $time)
        {
            // if we are not connected, stop
            if(!$this->is_connected())
            {
                return null;
            }

            // generate the request string
            $url =  $this->server_url . URI_SCHD_BRANDED_MESSAGE . "?connectionid=" . $this->connectionid;
            $url .= "&message=" . urlencode($message) . "&senderid=" . urlencode(substr($senderid,0,8)) . "&to=" . $this->array_to_string($mobiles) . "&timestamp=" . urlencode($time);

            // send the request off to the server
            if($this->send_request($url))
            {
                return $this->result;
            }
            else
            {
                return null;
            }
        }

        /**
         *  send a 2-way sms message through the s3 sms gateway
         *
         *  @param  $message    text message to send
         *  @param  $mobiles    array of valid mobile phone numbers
         *  @param  $messageid  an id that uniquely identifies this sms in your system
         *
         *  @return string      the message id returned fot this sms by
         *                      the s3 gateway
         */
        function send_two_way_sms($message, $mobiles, $messageid = null)
        {
            // if we are not connected, stop
            if(!$this->is_connected())
            {
                return null;
            }

            // generate the request string
            $url =  $this->server_url . URI_SEND_TWO_WAY_MESSAGE . "?connectionid=" . $this->connectionid;
            $url .= "&message=" . urlencode($message) . "&to=" . $this->array_to_string($mobiles);

            // add the message id if it is present
            if(!is_null($messageid))
            {
            	$messageid = str_ireplace(array(" ","-","(",")"), "", $messageid);
                $url .= "&messageid=" . urlencode($messageid);
            }

            // send the request off to the server
            if($this->send_request($url))
            {
                return $this->result;
            }
            else
            {
                return null;
            }
        }

        /**
         *  schedule a 2-way sms message through the s3 sms gateway to
         *	go out at a later date
         *
         *  @param  $message    text message to send
         *  @param  $mobiles    array of valid mobile phone numbers
         *  @param  $messageid  an id that uniquely identifies this sms in your system
         *	@param	$time		date/time to send the message represented as the number
         *						of seconds since the UNIX epoch (January 1 1970 00:00:00 GMT)
         *
         *  @return string      the message id returned fot this sms by
         *                      the s3 gateway
         */
        function schedule_two_way_sms($message, $mobiles, $messageid = null, $time)
        {
            // if we are not connected, stop
            if(!$this->is_connected())
            {
                return null;
            }

            // generate the request string
            $url =  $this->server_url . URI_SEND_TWO_WAY_MESSAGE . "?connectionid=" . $this->connectionid;
            $url .= "&message=" . urlencode($message) . "&to=" . $this->array_to_string($mobiles) . "&timestamp=" . urlencode($time);

            // add the message id if it is present
            if(!is_null($messageid))
            {
                $url .= "&messageid=" . urlencode($messageid);
            }

            // send the request off to the server
            if($this->send_request($url))
            {
                return $this->result;
            }
            else
            {
                return null;
            }
        }

        /**
         *  retrieve sms replies from the gateway
         *
         *  @param  $mark_as_read   mark the messages retrieved as "read"
         *  @param  $messageid      message id of the 2-way messages to search
         *                          for new replies for
         *
         *  @return array           an array of reply_sms objects
         */
        function get_sms_replies($mark_as_read = null, $messageid = null)
        {
            // if we are not connected, stop
            if(!$this->is_connected())
            {
                return null;
            }

            // generate the request string
            $url =  $this->server_url . URI_GET_REPLIES . "?connectionid=" . $this->connectionid;

            // add the mark_as_read param if anything
            // is passed in
            if(!is_null($mark_as_read))
            {
                $url .= "&mark_as_read=true";
            }

            // add the message id if it is present
            if(!is_null($messageid))
            {
                $url .= "&messageid=" . $messageid;
            }

            // send the request off to the server
            if($this->send_request($url))
            {
                // everything is well, we need to return an array of
                // sms reply objects
                $reply_count = $this->result;

                if($reply_count > 0)
                {
                    // there are replies
                    $result = array();

                    // we are expecting the response from the gateway to
                    // look like the following:
                    // id: msg_id  mobile: mob  message: msg   when: t
                    //
                    // which will be displayed in a fixed length manner

                    // go through and make the reply_sms objects
                    for($i = 1; $i <= $reply_count; $i++)
                    {
                        $line = $this->response[$i];

                        // get the message id
                        $start     = strlen(PREFIX_MESSAGEID);
                        $length    = LENGTH_MESSAGEID;
                        $messageid = trim(substr($line, $start, $length));

                        // get the mobile
                        $start    += $length + 1 + strlen(PREFIX_MOBILE);
                        $length    = LENGTH_MOBILE;
                        $mobile    = trim(substr($line, $start, $length));

                        // get the message
                        $start    += $length + 1 + strlen(PREFIX_MESSAGE);
                        $length    = LENGTH_MESSAGE;
                        $message   = trim(substr($line, $start, $length));

                        // get the time
                        $start    += $length + 1 + strlen(PREFIX_WHEN);
                        $when      = trim(substr($line, $start));

                        // create a new reply and place it in the array
                        $result[] = new reply_sms($messageid,
                                                  $message,
                                                  $mobile,
                                                  $when);
                    }

                    // change the result to the array
                    $this->result = $result;

                    // return the array
                    return $result;
                }
                else
                {
                    // no replies found, return an empty array
                    return array();
                }
            }
            else
            {
                // error
                return null;
            }
        }

        /**
         *  retrieve inbound sms from the gateway
         *
         *  @param  $mark_as_read   mark the messages retrieved as "read"
         *  @param  $inbound_number number to fetch the inbound messages for
         *
         *  @return array           an array of reply_sms objects
         */
        function get_inbound_sms($mark_as_read = null, $inbound_number = null)
        {
            // if we are not connected, stop
            if(!$this->is_connected())
            {
                return null;
            }

            // generate the request string
            $url =  $this->server_url . URI_GET_INBOUND_MESSAGES . "?connectionid=" . $this->connectionid;

            // add the mark_as_read param if anything
            // is passed in
            if(!is_null($mark_as_read))
            {
                $url .= "&mark_as_read=true";
            }

            // add the inbound number if it is present
            if(!is_null($inbound_number))
            {
                $url .= "&inbound_number=" . $inbound_number;
            }

            // send the request off to the server
            if($this->send_request($url))
            {
                // everything is well, we need to return an array of
                // sms reply objects
                $message_count = $this->result;

                if($message_count > 0)
                {
                    // there are replies
                    $result = array();

                    // we are expecting the response from the gateway to
                    // look like the following:
                    // inbound: inbound_number  mobile: mob  message: msg   when: t
                    //
                    // which will be displayed in a fixed length manner

                    // go through and make the inbound_sms objects
                    for($i = 1; $i <= $message_count; $i++)
                    {
                        $line = $this->response[$i];

                        // get the inbound number
                        $start          = strlen(PREFIX_INBOUND_NUMBER);
                        $length         = LENGTH_MOBILE;
                        $inbound_number = trim(substr($line, $start, $length));

                        // get the mobile
                        $start         += $length + 1 + strlen(PREFIX_MOBILE);
                        $length         = LENGTH_MOBILE;
                        $mobile         = trim(substr($line, $start, $length));

                        // get the message
                        $start         += $length + 1 + strlen(PREFIX_MESSAGE);
                        $length         = LENGTH_MESSAGE;
                        $message        = trim(substr($line, $start, $length));

                        // get the time
                        $start         += $length + 1 + strlen(PREFIX_WHEN);
                        $when           = trim(substr($line, $start));

                        // create a new reply and place it in the array
                        $result[] = new inbound_sms($inbound_number,
                                                    $message,
                                                    $mobile,
                                                    $when);
                    }

                    // change the result to the array
                    $this->result = $result;

                    // return the array
                    return $result;
                }
                else
                {
                    // no replies found, return an empty array
                    return array();
                }
            }
            else
            {
                // error
                return null;
            }
        }

        /**
         *  get your current credit balance
         *
         *  @return double  this will be -1 if an error is
         *                  encountered
         */
        function get_balance()
        {
            // if we are not connected, stop
            if(!$this->is_connected())
            {
                return -1;
            }

            // create the url
            $url =  $this->server_url . URI_GET_BALANCE . "?connectionid=" . $this->connectionid;

            // send the request off to the server
            if($this->send_request($url))
            {
                return $this->result;
            }
            else
            {
                // problem encountered, return -1
                return -1;
            }
        }

        /**
         *  disconnect from the s3 soap server
         *
         *  @return boolean
         */
        function disconnect()
        {
            // if we are not connected, stop
            if(!$this->is_connected())
            {
                return false;
            }

            // create the url
            $url =  $this->server_url . URI_DISCONNECT . "?connectionid=" . $this->connectionid;

            // send the request off to the server
            if($this->send_request($url))
            {
                return true;
            }
            else
            {
                // problem encountered
                return false;
            }
        }

        /**
         *  check if this sms_connection is connected to the gateway
         *
         *  @return boolean true if the connection has been established
         *                  and false otherwise
         */
        function is_connected()
        {
            return !is_null($this->connectionid);
        }

        /**
         *  return the result of the last operation
         *
         *  @return object  the result of the last operation
         *                  maybe a string, an int or an array
         */
        function get_result()
        {
            return $this->result;
        }

        /**
         *  check if there were any errors encountered whilst performing
         *  the last operation
         *
         *  @return boolean
         */
        function is_error()
        {
            return !is_null($this->error);
        }

        /**
         *  a helper function to return the error message associated with
         *  the last operation. if one is present
         *
         *  @return string
         */
        function get_error()
        {
            return $this->error;
        }

        /************************************************************************/
        /* private methods                                                      */
        /************************************************************************/

        /**
         *  a debug method used to show the response from the server
         *
         *  @return void
         */
        function print_response()
        {
            // nothing to print
            if(is_null($this->response))
            {
                return;
            }

            print("<pre>\n");

            for($i = 0; $i < count($this->response); $i++)
            {
                print($this->response[$i] . "\n");
            }

            print("</pre>\n");
        }

        /**
         *  send a http request to the url passed in
         *  and process the response.
         *
         *  the function will return true if the server
         *  is contactable and no errors are returned
         *
         *  @param  $url    the url to send the request to
         *
         *  @return boolean signals whether or not any errors
         *                  were encountered
         */
        function send_request($url)
        {
            // clear the result and error from the previous operation
            $this->result = null;
            $this->error  = null;

            // print("url = $url\n");

            // send the request and store the results in the response
            // attribute for debugging purposes
            if($this->response = @file($url))
            {
                // check if nothing was returned in the response
                if(count($this->response) == 0)
                {
                    $this->error = ERROR_INVALID_RESPONSE;

                    return false;
                }
                else
                {
                    // check the first line in the response.
                    // we are expecting a response of the form:
                    //
                    // id: a4c5ad77ad6faf5aa55f66a
                    //
                    // or
                    //
                    // credits: 2334
                    //
                    // or
                    //
                    // err: invalid login credentials
                    //
                    // etc...
                    $op_code   = $this->get_op_code($this->response[0]);
                    $op_result = $this->get_op_result($this->response[0]);

                    // print("op_code = '$op_code' op_result = '$op_result'\n");

                    // look for an error, i.e. err:
                    if($op_code == OP_CODE_ERR)
                    {
                        $this->error = $op_result;

                        return false;
                    }
                    else
                    {
                        // success
                        $this->result = $op_result;

                        return true;
                    }
                }
            }
            else
            {
                // gateway is not responding
                $this->error = ERROR_SMS_GATEWAY_UNREACHABLE;

                return false;
            }
        }

        /**
         *  look at the repsone returned by gateway and
         *  rerturn the portion of the line before the ":"
         *
         *  @param  $line       response from gateway
         *
         *  @return string
         */
        function get_op_code($line)
        {
            return strtolower(trim(substr($line, 0, strpos($line, ":"))));
        }

        /**
         *  look at the repsone returned by gateway and
         *  rerturn the portion of the line after the ":"
         *
         *  @param  $line       response from gateway
         *
         *  @return string
         */
        function get_op_result($line)
        {
            return trim(substr($line, strpos($line, ":") + 1));
        }

        /**
         *  helper function to turn the array of strings
         *  into one long string, with array elements separated
         *  by ","
         *
         *  @param  $elements   array of strings
         *
         *  @return string
         */
        function array_to_string($elements)
        {
            $result = "";

            for($i = 0, $count = count($elements); $i < $count; $i++)
            {
                $result .= str_ireplace(array(" ","-",".","(",")"), "", $elements[$i]);

                if($i + 1 < $count)
                {
                    $result .= ",";
                }
            }

            return $result;
        }
    }

    /****************************************************************************/
    /*                                                                          */
    /* desc:        class to encapsulate sms reply messages                     */
    /*                                                                          */
    /* version:     1.0                                                         */
    /* author:      ramez zaki                                                  */
    /* copyright:   copyright (c) 2001-2004. all rights reserved                */
    /* date:        17/08/2004                                                  */
    /*                                                                          */
    /****************************************************************************/
    class reply_sms
    {
        var $messageid; // the client specific id of the original 2-way message as
                        // submitted by the client in a call to send_two_way_sms()
        var $message;   // the sms message text
        var $mobile;    // mobile number responding
        var $when;      // the number of seconds since this reply was received

        /**
         *  create a new reply message
         */
        function reply_sms($messageid, $message, $mobile, $when)
        {
            $this->messageid = $messageid;
            $this->message   = $message;
            $this->mobile    = $mobile;
            $this->when      = $when;
        }

        /**
         *  show the contents of this reply
         *
         *  @return string
         */
        function to_string()
        {
            return "messageid = '" . $this->messageid . "' " .
                   "mobile = '" . $this->mobile . "' " .
                   "when = '" . $this->when . "' " .
                   "message = '" . $this->message . "'";
        }
    }

    /****************************************************************************/
    /*                                                                          */
    /* desc:        class to encapsulate inbound sms messages                   */
    /*                                                                          */
    /* version:     1.0                                                         */
    /* author:      ramez zaki                                                  */
    /* copyright:   copyright (c) 2001-2004. all rights reserved                */
    /* date:        24/07/2005                                                  */
    /*                                                                          */
    /****************************************************************************/
    class inbound_sms
    {
        var $inbound_number; // the number the message was received on
        var $message;        // the sms message text
        var $mobile;         // mobile number responding
        var $when;           // the number of seconds since this reply was received

        /**
         *  create a new inbound message
         */
        function inbound_sms($inbound_number, $message, $mobile, $when)
        {
            $this->inbound_number = $inbound_number;
            $this->message        = $message;
            $this->mobile         = $mobile;
            $this->when           = $when;
        }

        /**
         *  show the contents of this inbound message
         *
         *  @return string
         */
        function to_string()
        {
            return "inbound = '" . $this->inbound_number . "' " .
                   "mobile = '" . $this->mobile . "' " .
                   "when = '" . $this->when . "' " .
                   "message = '" . $this->message . "'";
        }
    }
?>