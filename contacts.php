<?php
// Database connection details
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'conatct_sys';

// Create database connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to add a new contact
function addContact($name, $phone, $email)
{
    global $conn;
    $stmt = $conn->prepare("INSERT INTO contacts (name, phone, email) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $phone, $email);
    $stmt->execute();
    $stmt->close();
}

// Function to edit an existing contact
function editContact($id, $name, $phone, $email)
{
    global $conn;
    $stmt = $conn->prepare("UPDATE contacts SET name=?, phone=?, email=? WHERE id=?");
    $stmt->bind_param("sssi", $name, $phone, $email, $id);
    $stmt->execute();
    $stmt->close();
}

// Function to delete a contact
function deleteContact($id)
{
    global $conn;
    $stmt = $conn->prepare("DELETE FROM contacts WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        switch ($action) {
            case 'add':
                addContact($_POST['name'], $_POST['phone'], $_POST['email']);
                break;
            case 'edit':
                // If the edit form is submitted, handle the update
                if (isset($_POST['edit_submit'])) {
                    editContact($_POST['id'], $_POST['edit_name'], $_POST['edit_phone'], $_POST['edit_email']);
                }
                break;
            case 'delete':
                deleteContact($_POST['id']);
                break;
        }
    }
}

// Fetch all contacts
$contacts = array();
$result = $conn->query("SELECT * FROM contacts");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $contacts[] = $row;
    }
}

// Close database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="contacts.css">
    <title>Contact Manager</title>
</head>

<body>
    <h1>Contact Manager</h1>

    <!-- Form to add a new contact -->
    <h2>Add Contact</h2>
    <form method="post">
        <input type="hidden" name="action" value="add">
        <label for="name">Name:</label><br>
        <input type="text" id="name" name="name" required><br>
        <label for="phone">Phone:</label><br>
        <input type="text" id="phone" name="phone" required><br>
        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" required><br>
        <button type="submit">Add Contact</button>
    </form>

    <!-- Display contact list -->
    <h2>Contact List</h2>
    <ul>
        <?php foreach ($contacts as $contact) : ?>
            <li>
                <?php echo $contact['name']; ?> | <?php echo $contact['phone']; ?> | <?php echo $contact['email']; ?>
                <!-- Edit form with pre-filled input fields -->
                <form method="post" style="display: inline;">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" value="<?php echo $contact['id']; ?>">
                    <input type="hidden" name="edit_name" value="<?php echo $contact['name']; ?>">
                    <input type="hidden" name="edit_phone" value="<?php echo $contact['phone']; ?>">
                    <input type="hidden" name="edit_email" value="<?php echo $contact['email']; ?>">
                    <button type="submit">Edit</button>
                </form>
                <form method="post" style="display: inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo $contact['id']; ?>">
                    <button type="submit">Delete</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>

    <!-- Edit form -->
    <?php if (isset($_POST['action']) && $_POST['action'] === 'edit') : ?>
        <?php
        $editContactId = $_POST['id'];
        $editContact = array_filter($contacts, function ($contact) use ($editContactId) {
            return $contact['id'] == $editContactId;
        });
        $editContact = reset($editContact);
        ?>
        <h2>Edit Contact</h2>
        <form method="post">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" value="<?php echo $editContact['id']; ?>">
            <label for="edit_name">Name:</label><br>
            <input type="text" id="edit_name" name="edit_name" value="<?php echo htmlspecialchars($editContact['name']); ?>" required><br>
            <label for="edit_phone">Phone:</label><br>
            <input type="text" id="edit_phone" name="edit_phone" value="<?php echo htmlspecialchars($editContact['phone']); ?>" required><br>
            <label for="edit_email">Email:</label><br>
            <input type="email" id="edit_email" name="edit_email" value="<?php echo htmlspecialchars($editContact['email']); ?>" required><br>
            <button type="submit" name="edit_submit">Update Contact</button>
        </form>
    <?php endif; ?>
</body>

</html>