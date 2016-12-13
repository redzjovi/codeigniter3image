<html>
<head>
    <title>Upload Form</title>
</head>
<body>
    <h3>Your file was successfully uploaded!</h3>

    <ol>
        <?php foreach ($message as $value) : ?>
            <li><?php echo $value ?></li>
        <?php endforeach ?>
    </ol>

    <p><?php echo anchor('uploads', 'Open Folder', array('target' => '_blank')); ?></p>

    <p><?php echo anchor('upload', 'Upload Another File!'); ?></p>
</body>
</html>
