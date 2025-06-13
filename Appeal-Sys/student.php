<?php
// Assume $pdo is your database connection object, and $text and $phoneNumber are provided by the USSD gateway.

// Initial check for empty text to display the main menu
if ($text == "") {
    echo "CON Welcome to the Marks Appeal System\n";
    echo "1. Check my marks\n";
    echo "2. Appeal my marks\n";
    echo "3. Check appeal status\n";
    echo "0. Exit";
} else {
    $input = explode("*", $text);
    $mainChoice = $input[0]; // The user's selection from the main menu (e.g., "1", "2", "3")
    $level = count($input);   // The current step within a chosen option

    switch ($mainChoice) {
        case "1": // Check my marks
            switch ($level) {
                case 1:
                    // FIX: Prompt for student ID
                    echo "CON Enter your student ID:";
                    break;
                case 2:
                    $studentId = $input[1]; // Get student ID from user input

                    // Validate student ID exists
                    $stmt = $pdo->prepare("SELECT student_id FROM students WHERE student_id = ?");
                    $stmt->execute([$studentId]);
                    $studentExists = $stmt->fetch();

                    if (!$studentExists) {
                        echo "END Error: Student ID not found.";
                        exit;
                    }

                    $stmt = $pdo->prepare("
                        SELECT m.module_name, k.mark
                        FROM marks k
                        JOIN modules m ON k.module_id = m.id
                        WHERE k.student_id = ?
                    ");
                    $stmt->execute([$studentId]);
                    $results = $stmt->fetchAll();

                    if (!$results) {
                        echo "END No marks found for ID {$studentId}.";
                    } else {
                        $response = "Your Marks for ID {$studentId}:\n";
                        foreach ($results as $row) {
                            $response .= "{$row['module_name']}: {$row['mark']}\n";
                        }
                        echo "END " . $response; // End the session after displaying marks
                    }
                    break;
                default:
                    echo "END Invalid input for checking marks.";
                    break;
            }
            break;

        case "2": // Appeal my marks
            switch ($level) {
                case 1:
                    // FIX: Prompt for student ID
                    echo "CON Enter your student ID:";
                    break;
                case 2:
                    $studentId = $input[1]; // Get student ID from user input

                    // Validate student ID exists
                    $stmt = $pdo->prepare("SELECT student_id FROM students WHERE student_id = ?");
                    $stmt->execute([$studentId]);
                    $studentExists = $stmt->fetch();

                    if (!$studentExists) {
                        echo "END Error: Student ID not found.";
                        exit;
                    }
                    
                    // Store studentId temporarily for the next steps of appeal
                    // A session variable or similar mechanism would be used in a real app,
                    // but for USSD, it's typically passed in the `$text` string.
                    // The current `$input` array handles this naturally.

                    // Show modules to appeal for the entered student ID
                    $stmt = $pdo->prepare("
                        SELECT m.id, m.module_code, m.module_name, k.mark
                        FROM marks k
                        JOIN modules m ON k.module_id = m.id
                        WHERE k.student_id = ?
                    ");
                    $stmt->execute([$studentId]);
                    $modules = $stmt->fetchAll();

                    if (!$modules) {
                        echo "END No modules found for appeal for ID {$studentId}.";
                        exit;
                    }

                    $response = "CON Select module to appeal (ID {$studentId}):\n";
                    foreach ($modules as $i => $mod) {
                        $response .= ($i + 1) . ". {$mod['module_name']} ({$mod['mark']})\n";
                    }
                    echo $response;
                    break;
                case 3:
                    $studentId = $input[1]; // Student ID from previous step
                    $selectedIndex = (int)$input[2] - 1; // User's choice of module

                    // Re-fetch modules to validate selection
                    $stmt = $pdo->prepare("
                        SELECT m.id, m.module_name, k.mark
                        FROM marks k
                        JOIN modules m ON k.module_id = m.id
                        WHERE k.student_id = ?
                    ");
                    $stmt->execute([$studentId]);
                    $modules = $stmt->fetchAll();

                    if (!isset($modules[$selectedIndex])) {
                        echo "END Invalid module selection. Please try again.";
                        exit;
                    }

                    $selectedModuleId = $modules[$selectedIndex]['id'];
                    $selectedModuleName = $modules[$selectedIndex]['module_name'];

                    echo "CON Enter your reason for appealing {$selectedModuleName}:";
                    break;
                case 4:
                    $studentId = $input[1]; // Student ID
                    $selectedIndex = (int)$input[2] - 1; // Module index
                    $reason = $input[3]; // Reason for appeal

                    // Re-fetch modules to get the correct module ID
                    $stmt = $pdo->prepare("
                        SELECT m.id FROM marks k
                        JOIN modules m ON k.module_id = m.id
                        WHERE k.student_id = ?
                    ");
                    $stmt->execute([$studentId]);
                    $modules = $stmt->fetchAll();

                    if (!isset($modules[$selectedIndex])) {
                        echo "END Error processing appeal. Please try again.";
                        exit;
                    }
                    $moduleId = $modules[$selectedIndex]['id'];

                    // Insert appeal
                    $stmt = $pdo->prepare("
                        INSERT INTO appeals (student_id, module_id, reason, status)
                        VALUES (?, ?, ?, 'pending')
                    ");
                    $stmt->execute([$studentId, $moduleId, $reason]);

                    echo "END Thank you. Your appeal for {$modules[$selectedIndex]['module_name']} has been submitted.";
                    break;
                default:
                    echo "END Invalid input for appealing marks.";
                    break;
            }
            break;

        case "3": // Check appeal status
            switch ($level) {
                case 1:
                    // FIX: Prompt for student ID
                    echo "CON Enter your student ID:";
                    break;
                case 2:
                    $studentId = $input[1]; // Get student ID from user input

                    // Validate student ID exists
                    $stmt = $pdo->prepare("SELECT student_id FROM students WHERE student_id = ?");
                    $stmt->execute([$studentId]);
                    $studentExists = $stmt->fetch();

                    if (!$studentExists) {
                        echo "END Error: Student ID not found.";
                        exit;
                    }

                    $stmt = $pdo->prepare("
                        SELECT m.module_name, a.status
                        FROM appeals a
                        JOIN modules m ON a.module_id = m.id
                        WHERE a.student_id = ?
                    ");
                    $stmt->execute([$studentId]);
                    $appeals = $stmt->fetchAll();

                    if (!$appeals) {
                        echo "END You have no appeals submitted for ID {$studentId}.";
                    } else {
                        $response = "Appeal Status for ID {$studentId}:\n";
                        foreach ($appeals as $a) {
                            $response .= "{$a['module_name']}: {$a['status']}\n";
                        }
                        echo "END " . $response; // End the session after displaying status
                    }
                    break;
                default:
                    echo "END Invalid input for checking appeal status.";
                    break;
            }
            break;

        case "0": // Exit
            echo "END Thank you for using the system.";
            break;

        default:
            echo "END Invalid option selected. Please try again.";
            break;
    }
}
?>