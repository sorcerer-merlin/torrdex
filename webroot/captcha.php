<?php
    // Put out the header
    $pageTitle = "Security Verification";
    require_once('header.php');
    require_once 'PasswordHash.php';

    // If we didn't specify a mode, redirect to the login page
    if (!isset($_POST['mode'])) echo '<script type="text/javascript">window.location = "/login"</script>';

    // Get the mode and handle it
    $Mode = $_POST['mode'];

    if ($Mode == "pass_reset") {
        if (!isset($_POST['user']) || !isset($_POST['email']) || !isset($_POST['code']) || !isset($_POST['time']) || !isset($_POST['captcha']) || !isset($_POST['newpass']) || !isset($_POST['newpass2']))
            showError("Improper parameters were passed! Please contact your Administrator.");
        else {
            // We are good so get the variables
            $User = $_POST['user'];
            $Email = $_POST['email'];
            $Time = $_POST['time'];
            $Code = $_POST['code'];
            $Captcha = $_POST['captcha'];
            $NewPass = $_POST['newpass'];
            $NewPass2 = $_POST['newpass2'];

            // Create the re-try link
            // forgot?mode=do_reset&user=merlin&email=jman.rice.cakes@gmail.com&code=f27f2f930f6b29e7170a5c33effe9e256f26fbfce6fffaf5b0
            $RetryLink = "forgot?mode=do_reset&user=" . $User . "&email=" . $Email . "&code=" . $Code;

            // Now check the database for the entry we want
            $result = queryMySQL("SELECT * FROM forgot WHERE user='$User' AND email='$Email' AND code='$Code' AND expires='$Time';");
            if ($result->num_rows == 0)
                showError("Improper parameters were passed! Please contact your Administrator.");
            else {
                // Check the time code
                $Now = time();
                if ($Now <= $Time) {
                    // Now we need to check the CAPTCHA code, and then the passwords matching and finally do the update and remove the
                    // original code set from forgot table.
                    if ($Captcha != $_SESSION['captcha']['code'])
                        showError("Your code did not match the verification code displayed. Please <a href='$RetryLink'>try again</a>.");
                    else {
                        // CAPTCHA code is all good, check the passwords
                        if ($NewPass != $NewPass2)
                            showError("Your passwords do not match. Please <a href='$RetryLink'>try again</a>.");
                        else {
                            // We have matching code, and matching passwords... finally do the fin reset
                            $PasswordHashed = PasswordHash::create_hash($NewPass); 
                            $result = queryMySQL("UPDATE members SET pass='$PasswordHashed' WHERE user='$User';");
                            if (!$result)
                                showError("There was an error updating your password in the database. Please contact your Administrator.");
                            else {
                                // we have successfully changed their password. Now remove the FORGOT table entry and give them a link to 
                                // get to the freakin login page again
                                $result = queryMySQL("DELETE FROM forgot WHERE user='$User' AND email='$Email' AND code='$Code' AND expires='$Time';");
                                echo "<h3>Success!</h3><br>Your password has been <b>successfully</b> changed, you can now <a href='login'>login</a> again!";
                            }
                        }
                    }
                } else {
                    // They have an expired time. Remove their entry (below needs to be checked to make sure its SQL valid and works),
                    // and send them back to the first page.
                    $result = queryMySQL("DELETE FROM forgot WHERE user='$User' AND email='$Email' AND code='$Code' AND expires='$Time';");
                    showError("You have run out of time, your link has expired. Please <a href='forgot?mode=form'>try again</a>.");
                }
            }
        }
    }

    // Put out the footer
    require_once('footer.php');
?>