<!-- resources/views/welcome.blade.php -->
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
    <link href="<?php echo e(asset('css/app.css')); ?>" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body>
    <div class="container text-center mt-5">
        <h1>Welcome to  Kundi Human Resources Management and Payroll</h1>
        <p>A comprehensive solution for managing human resources and payroll.</p>
        <a href="<?php echo e(route('login')); ?>" class="btn btn-primary">Login</a>
    </div>
    <script src="<?php echo e(asset('js/app.js')); ?>"></script>
</body>
</html><?php /**PATH C:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views/welcome.blade.php ENDPATH**/ ?>