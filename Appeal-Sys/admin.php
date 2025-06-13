<?php
// Assume $pdo is your database connection object, and $text and $phoneNumber are provided by the USSD gateway.

// Check if the user is a registered admin
$stmt = $pdo->prepare("SELECT * FROM admins WHERE phone_number = ?");
$stmt->execute([$phoneNumber]);
$isAdmin = $stmt->fetch();

if (!$isAdmin) {
    echo "END Access Denied. You are not authorized to use this service.";
    exit;
}

// If $text is empty, it's the first interaction. Show the main menu.
if ($text == "") {
    echo "CON Admin Panel:\n";
    echo "1. Register student\n";
    echo "2. Register module\n";
    echo "3. Insert marks\n";
    echo "4. View appeals\n";
    echo "5. Resolve appeal\n";
    echo "0. Exit";
} else {
    // If $text is not empty, process the user's input.
    $input = explode("*", $text);
    $mainChoice = $input[0]; // The user's selection from the main menu
    $level = count($input); // The current step within a menu option

    switch ($mainChoice) {
        case "1": // Register Student
            switch ($level) {
                case 1:
                    echo "CON Enter student ID:";
                    break;
                case 2:
                    echo "CON Enter student name:";
                    break;
                case 3:
                    // FIX: Asking for student's phone number instead of using admin's.
                    echo "CON Enter student phone number (e.g., 25078...):";
                    break;
                case 4:
                    $studentId = $input[1];
                    $studentName = $input[2];
                    $studentPhone = $input[3]; // Use the number entered in the previous step.

                    $stmt = $pdo->prepare("INSERT INTO students (student_id, name, phone_number) VALUES (?, ?, ?)");
                    $stmt->execute([$studentId, $studentName, $studentPhone]);
                    echo "END Student registered successfully.";
                    break;
                default:
                    echo "END Invalid input.";
                    break;
            }
            break;

        case "2": // Register Module
            switch ($level) {
                case 1:
                    // FIX: Asking for module code first.
                    echo "CON Enter module code:";
                    break;
                case 2:
                    echo "CON Enter module name:";
                    break;
                case 3:
                    $moduleCode = $input[1];
                    $moduleName = $input[2];

                    // FIX: Inserting both module code and name.
                    // Note: Assumes your 'modules' table has 'module_code' and 'module_name' columns.
                    $stmt = $pdo->prepare("INSERT INTO modules (module_code, module_name) VALUES (?, ?)");
                    $stmt->execute([$moduleCode, $moduleName]);
                    echo "END Module registered successfully.";
                    break;
                default:
                    echo "END Invalid input.";
                    break;
            }
            break;

        case "3": // Insert Marks
            switch ($level) {
                case 1:
                    echo "CON Enter student ID to assign mark:";
                    break;
                case 2:
                    echo "CON Enter module code:";
                    break;
                case 3:
                    echo "CON Enter mark (0-100):";
                    break;
                case 4:
                    $studentId = $input[1];
                    $moduleCode = $input[2];
                    $mark = $input[3];

                    // Get module ID from module code
                    $stmt = $pdo->prepare("SELECT id FROM modules WHERE module_code = ?");
                    $stmt->execute([$moduleCode]);
                    $module = $stmt->fetch();

                    if (!$module) {
                        echo "END Module with code '$moduleCode' not found.";
                        exit;
                    }

                    $stmt = $pdo->prepare("INSERT INTO marks (student_id, module_id, mark) VALUES (?, ?, ?)");
                    $stmt->execute([$studentId, $module['id'], $mark]);
                    echo "END Mark inserted successfully.";
                    break;
                default:
                    echo "END Invalid input.";
                    break;
            }
            break;

        case "4": // View Appeals
            $stmt = $pdo->query("
                SELECT s.student_id, m.module_code, a.reason, a.status
                FROM appeals a
                JOIN students s ON a.student_id = s.student_id
                JOIN modules m ON a.module_id = m.id
                ORDER BY a.status ASC
            ");
            $rows = $stmt->fetchAll();

            if (!$rows) {
                echo "END No appeals found.";
            } else {
                $response = "Appeals:\n";
                foreach ($rows as $row) {
                    $response .= "{$row['student_id']}-{$row['module_code']}({$row['status']}): {$row['reason']}\n";
                }
                echo "END " . $response;
            }
            break;

        case "5": // Resolve Appeal
            // Fetch pending appeals to allow selection
            $stmt = $pdo->prepare("
                SELECT a.id, s.student_id, m.module_code
                FROM appeals a
                JOIN students s ON a.student_id = s.student_id
                JOIN modules m ON a.module_id = m.id
                WHERE a.status = 'pending'
            ");
            $stmt->execute();
            $appeals = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($appeals)) {
                echo "END No pending appeals found.";
                exit;
            }

            switch ($level) {
                case 1:
                    $response = "CON Select appeal to resolve:\n";
                    foreach ($appeals as $index => $appeal) {
                        $response .= ($index + 1) . ". {$appeal['student_id']} - {$appeal['module_code']}\n";
                    }
                    echo $response;
                    break;
                case 2:
                    echo "CON Enter 1 to Approve, 2 to Reject:";
                    break;
                case 3:
                    $selectedIndex = (int)$input[1] - 1; // User's choice
                    $decision = $input[2] == "1" ? "approved" : "rejected";

                    if (!isset($appeals[$selectedIndex])) {
                        echo "END Invalid selection.";
                        exit;
                    }

                    $appealId = $appeals[$selectedIndex]['id'];
                    $stmt = $pdo->prepare("UPDATE appeals SET status = ? WHERE id = ?");
                    $stmt->execute([$decision, $appealId]);

                    echo "END Appeal has been successfully $decision.";
                    break;
                default:
                    echo "END Invalid input.";
                    break;
            }
            break;
        
        case "0": // Exit
             echo "END Thank you for using the Admin Panel.";
             break;

        default:
            echo "END Invalid option selected.";
            break;
    }
}
?>