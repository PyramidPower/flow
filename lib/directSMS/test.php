<html>
    <title>directSMS PHP Code Sample</title>
    <body>
        <h2>directSMS PHP Code Sample</h2>
        <pre>
<?php

    /****************************************************************************/
    /*                                                                          */
    /* desc:        sample to show the operations available through the         */
    /*              directsms http api                                          */
    /*                                                                          */
    /* version:     1.0                                                         */
    /* author:      ramez zaki                                                  */
    /* copyright:   copyright (c) 2001-2004. all rights reserved                */
    /* date:        17/08/2004                                                  */
    /*                                                                          */
    /****************************************************************************/

    // load the directsms sms_connection library
    require_once("sms_connection.php");

    // create a new connection
    $conn = new sms_connection();

    // check if there are any problems. at this stage
    // the only problem at this stage might be that
    // the sms gateway is unreachable
    if($conn->is_error())
    {
        print("ERROR: " . $conn->get_error() . "\n");
    }
    else
    {
        // login and start a session 
        //
        // if($conn->connect("s3_user", "Pa55w0rd"))
        //
        // use the licence key method
        if($conn->connect("s3_user", "Pa55w0rd", "71904d3a-d4c5ab00-46dbf93a-1f36312d"))
        {
            // get the current balance
            $credits = $conn->get_balance();

            // use the is_error() method to check for
            // any problems
            if($conn->is_error())
            {
                // show the error encountered
                print("ERROR: " . $conn->get_error() . "\n");
            }
            else
            {
                // show the balance
                print("current credit balance = " . $credits . " credit(s)\n");
            }

            // create a new sms message
            $message  = "this is a test message from the directsms sms gateway";
            $mobiles  = array("0401001001", "0402002002");

            // send a branded sms message with the sender id  "test.123"
            // please note that branded messages are a little picky about
            // the sender id. we must keep our choice of characters to
            // a-z, A-Z, . or 0-9 only. the sender id must not exceed 11 
            // characters
            $id = $conn->send_branded_sms($message, $mobiles, "test.123");

            if($conn->is_error())
            {
                print("error sending branded message = " . $conn->get_error() . "\n");
            }
            else
            {
                print("branded message id = $id\n");
            }

            // send a 2-way sms message with the message id "my-id". we can
            // use that to search for replies to this message later, or at
            // least to correlate reply messages to this 2-way message later
            // on
            $id = $conn->send_two_way_sms($message, $mobiles, "my-id");

            if($conn->is_error())
            {
                print("error sending 2-way message = " . $conn->get_error() . "\n");
            }
            else
            {
                print("2-way message id = $id\n");
            }

            // lets retrieve any replies to the 2-way sms submitted just
            // now
            $replies = $conn->get_sms_replies(null, "my-id");

            print(count($replies) . " replies retrieved\n");

            // display the replies
            for($i = 0; $i < count($replies); $i++)
            {
                // print each reply on a separate line.
                print($replies[$i]->to_string() . "\n");
            }

            // lets retrieve any new inbound sms messages that
            // have come in
            $inbound_sms = $conn->get_inbound_sms();

            print(count($inbound_sms) . " inbound sms retrieved\n");

            // display the messages
            for($i = 0; $i < count($inbound_sms); $i++)
            {
                // print each message on a separate line.
                print($inbound_sms[$i]->to_string() . "\n");
            }
            
            // schedule a message to go out at 11 AM on Jan 1 
            // of next year to say happy new year
            $time = mktime(11, 0, 0, 1, 1, date("Y") + 1);

            // the message            
            $message = "Happy new year from the team @ directSMS team";
            
            // the mobiles
            $mobiles = array("0407263977", "0414574496");
            
            // save away
            $id = $conn->schedule_branded_sms($message, $mobiles, "directSMS", $time);

            if($conn->is_error())
            {
                print("error scheduling new branded message = " . $conn->get_error() . "\n");
            }
            else
            {
                print("scheduled message id = $id\n");
            }

            // lets look at our credits again,
            print("current credit balance = " . $conn->get_balance() . " credit(s)\n");

            // done, now disconnect
            $conn->disconnect();
        }
        else
        {
            // show the error and stop
            print("ERROR: " . $conn->get_error() . "\n");
        }
    }
?>
        </pre>
    </body>
</html>
