<?php
/* ---------------------------------------------------------------------
 *  Create an account.
 *  Creates the user row and its wallet row inside one transaction, so a
 *  user can never exist without an account to hold money.
 * ------------------------------------------------------------------- */
require_once __DIR__ . '/config/helpers.php';

if (is_logged_in()) {
    redirect('account.php');
}

$errors = [];
$old    = ['Phone' => '', 'Firstname' => '', 'Lastname' => '', 'Nid' => '',
           'Email' => '', 'Dob' => '', 'Gender' => 'Male'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    foreach ($old as $k => $_) {
        $old[$k] = post($k);
    }

    $phone     = $old['Phone'];
    $firstname = ucwords(strtolower($old['Firstname']));
    $lastname  = ucwords(strtolower($old['Lastname']));
    $nid       = $old['Nid'];
    $email     = strtolower($old['Email']);
    $dob       = $old['Dob'];
    $gender    = in_array($old['Gender'], ['Male', 'Female', 'Others'], true)
               ? $old['Gender'] : 'Others';
    $password  = post('Password');
    $confirm   = post('ConfirmPassword');
    $pin       = post('Pin');
    $pinAgain  = post('PinConfirm');

    /* ---- validation ------------------------------------------------ */
    if (!valid_phone($phone)) {
        $errors['Phone'] = 'Enter a valid 11 digit Bangladeshi number, for example 01712345678.';
    }
    if ($firstname === '' || $lastname === '') {
        $errors['Firstname'] = 'Please enter your full name.';
    }
    if (!in_array(strlen($nid), [10, 13, 17, 18], true) || !ctype_digit($nid)) {
        $errors['Nid'] = 'NID must be 10, 13, 17 or 18 digits.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['Email'] = 'Enter a valid email address.';
    }
    if ($dob === '') {
        $errors['Dob'] = 'Please enter your date of birth.';
    } else {
        try {
            $age = (new DateTime())->diff(new DateTime($dob))->y;
            if ($age < 18) {
                $errors['Dob'] = 'You must be at least 18 years old to open an account.';
            } elseif ($age > 120) {
                $errors['Dob'] = 'Please check the date of birth.';
            }
        } catch (Exception $e) {
            $errors['Dob'] = 'That is not a valid date.';
        }
    }
    if (strlen($password) < 6) {
        $errors['Password'] = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $errors['ConfirmPassword'] = 'The two passwords do not match.';
    }
    if (!valid_pin($pin)) {
        $errors['Pin'] = 'The transaction PIN must be exactly 6 digits.';
    } elseif ($pin !== $pinAgain) {
        $errors['PinConfirm'] = 'The two PINs do not match.';
    } elseif (preg_match('/^(\d)\1{5}$/', $pin) || in_array($pin, ['123456', '654321'], true)) {
        $errors['Pin'] = 'Please choose a less obvious PIN.';
    }

    /* ---- uniqueness ------------------------------------------------ */
    if (!$errors) {
        if (scalar('SELECT 1 FROM user WHERE phone = ?', [$phone])) {
            $errors['Phone'] = 'This number is already registered.';
        }
        if (scalar('SELECT 1 FROM user WHERE email = ?', [$email])) {
            $errors['Email'] = 'This email is already registered.';
        }
        if (scalar('SELECT 1 FROM user WHERE nid = ?', [$nid])) {
            $errors['Nid'] = 'This NID is already registered.';
        }
    }

    /* ---- create ----------------------------------------------------- */
    if (!$errors) {
        $palette = ['#1E3A63', '#A87A2B', '#A9412C', '#5A4A86', '#C09A4E', '#2C6E4E'];
        $colour  = $palette[array_rand($palette)];

        $pdo = db();
        $pdo->beginTransaction();
        try {
            q('INSERT INTO user
                 (phone, firstname, lastname, nid, email, password_hash, pin_hash,
                  dob, gender, avatar_color, daily_limit)
               VALUES (?,?,?,?,?,?,?,?,?,?,?)',
              [$phone, $firstname, $lastname, $nid, $email,
               password_hash($password, PASSWORD_BCRYPT),
               password_hash($pin, PASSWORD_BCRYPT),
               $dob, $gender, $colour, DAILY_LIMIT_DEFAULT]);

            q('INSERT INTO account (phone, balance) VALUES (?, 0)', [$phone]);
            $pdo->commit();

            notify($phone, 'Welcome to ' . APP_NAME,
                'Your account is ready. Add money to get started.', 'gift');
            audit($phone, 'signup', 'account created');
            flash('ok', 'Account created. Please sign in.');
            redirect('login.php');

        } catch (Throwable $err) {
            $pdo->rollBack();
            $errors['Phone'] = 'Could not create the account. Please try again.';
        }
    }
}

include('Front_end/front_signup.php');
