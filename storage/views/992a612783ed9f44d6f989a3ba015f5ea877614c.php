<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Hello World</title>
</head>
<body>
    <h1>Hello World</h1>
    <?php echo $__env->make('info', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <a href="<?php echo e(route('user', ['name' => 'Doan', 'age' => 28, 'gender' => 'male', 'status'=>'FA'])); ?>">Doãn</a>
</body>
</html><?php /**PATH E:\Websites\toiladev.vn\crazy\views/hello-world.blade.php ENDPATH**/ ?>