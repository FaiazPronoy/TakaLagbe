<?php
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/lib/money.php';

$phone = require_login();
$bal   = balance_of($phone);
$error = '';

$institutes = all('SELECT * FROM institutes ORDER BY institute_name');

$semesters = [];
foreach (['Spring', 'Summer', 'Fall'] as $s) {
    for ($y = (int) date('Y'); $y >= (int) date('Y') - 1; $y--) {
        $semesters[] = "$s $y";
    }
}
sort($semesters);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    require_fresh('education_fee');
    $instituteId = post('institute_id');
    $studentId   = post('student_id');
    $studentName = ucwords(strtolower(post('student_name')));
    $semester    = post('semester');
    $amount      = post('amount');
    $pin         = post('pin');

    $inst = one('SELECT * FROM institutes WHERE institute_id = ?', [$instituteId]);

    if (!$inst) {
        $error = __('Please choose an institute.');
    } elseif (!preg_match('/^[0-9A-Za-z\-]{4,30}$/', $studentId)) {
        $error = __('Enter a valid student ID.');
    } elseif ($studentName === '') {
        $error = __('Enter the student name.');
    } elseif (!in_array($semester, $semesters, true)) {
        $error = __('Please choose a semester.');
    } elseif (!valid_amount($amount)) {
        $error = __('Enter a valid amount.');
    } elseif ((float) $amount < MIN_AMOUNT) {
        $error = __('The smallest payment is {amount}.', ['amount' => money(MIN_AMOUNT)]);
    } elseif (scalar('SELECT 1 FROM education_fee
                      WHERE institute_id = ? AND student_id = ? AND semester = ?',
                     [$instituteId, $studentId, $semester])) {
        $error = __('This semester fee has already been paid for that student ID.');
    } elseif (!verify_pin($phone, $pin)) {
        $left  = pin_tries_left($phone);
        $error = __('Wrong transaction PIN.') . ($left > 0 ? ' ' . __('{n} attempt(s) left.', ['n' => $left]) : '');
    } else {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $trx = move_money([
                'from'     => $phone,
                'to'       => null,
                'to_ref'   => $inst['institute_name'],
                'amount'   => (float) $amount,
                'type'     => 'Education Fee',
                'category' => 'Education',
                'note'     => $semester . ' fee for ' . $studentId,
            ]);

            q('INSERT INTO education_fee
                 (payer_phone, institute_id, institute_name, student_id, student_name,
                  semester, amount, trx_id)
               VALUES (?,?,?,?,?,?,?,?)',
              [$phone, $instituteId, $inst['institute_name'], $studentId, $studentName,
               $semester, (float) $amount, $trx['trx_id']]);

            $pdo->commit();
            flash('ok', __('{amount} paid to {name}.', ['amount' => money($amount), 'name' => $inst['institute_name']]));
            redirect('receipt.php?id=' . urlencode($trx['trx_id']));

        } catch (MoneyError $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            record_refusal($e);
            $error = $e->getMessage();
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            $error = ($e->errorInfo[1] ?? 0) === 1062
                ? 'This semester fee has already been paid for that student ID.'
                : 'The fee could not be paid. Please try again.';
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            $error = __('The fee could not be paid. Please try again.');
        }
    }
}

$recent = all('SELECT * FROM education_fee WHERE payer_phone = ?
               ORDER BY timestamp DESC LIMIT 5', [$phone]);

$pageTitle = __('Education fee');
$activeNav = 'home';
include('views/education_fee.php');
