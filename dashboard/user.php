<?php
include('includes/header.php');
include('session.php');
include_once('db.php');

// Function to get all users
function getAllUsers() {
    global $conn;
    $sql = "SELECT * FROM users";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Delete user
if (isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
}

// Update user
if (isset($_POST['update_user'])) {
    $user_id = $_POST['user_id'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    $sql = "UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $username, $email, $role, $user_id);
    $stmt->execute();
    $stmt->close();
}

$users = getAllUsers();
?>

<div class="dashboard-container">
    <?php include('includes/sidebar.php'); ?>
    <main>
        <div class="dashboard-content">
            <h1>User Management</h1>
            <div class="user-table">
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Action</th>
                    </tr>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo $user['username']; ?></td>
                        <td><?php echo $user['email']; ?></td>
                        <td><?php echo $user['role']; ?></td>
                        <td>
                            <button onclick="openUpdateModal(<?php echo $user['id']; ?>, '<?php echo $user['username']; ?>', '<?php echo $user['email']; ?>', '<?php echo $user['role']; ?>')">Update</button>
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <button type="submit" name="delete_user" onclick="return confirm('Are you sure you want to delete this user?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- Update User Modal -->
<div id="updateModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Update User</h2>
        <form id="updateForm" method="post">
            <input type="hidden" id="update_user_id" name="user_id">
            <label for="update_username">Username:</label>
            <input type="text" id="update_username" name="username" required>
            
            <label for="update_email">Email:</label>
            <input type="email" id="update_email" name="email" required>
            
            <label for="update_role">Role:</label>
            <select id="update_role" name="role">
                <option value="user">User</option>
                <option value="admin">Admin</option>
            </select>
            
            <button type="submit" name="update_user">Update User</button>
        </form>
    </div>
</div>

<script>
    var modal = document.getElementById("updateModal");
    var span = document.getElementsByClassName("close")[0];

    function openUpdateModal(id, username, email, role) {
        document.getElementById("update_user_id").value = id;
        document.getElementById("update_username").value = username;
        document.getElementById("update_email").value = email;
        document.getElementById("update_role").value = role;
        modal.style.display = "block";
    }

    span.onclick = function() {
        modal.style.display = "none";
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
</script>

<?php include('includes/footer.php'); ?>