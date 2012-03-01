<div class="wrap">
	<h2><?php _e( 'Import users from an XML file' , 'import-users-from-xml'); ?></h2>
    
    <?php if ( !empty( $message ) ): ?>
        <?php echo $message;?>    
    <?php endif;?>

    
    <?php if( $show_form == true ): ;?>    
        
        <form method="post" action="" enctype="multipart/form-data">
        <?php wp_nonce_field( 'iu-fx-xml_import', '_wpnonce-iu-fx-xml_import' ); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><label for"xml_file"><?php _e( 'XML file' , 'import-users-from-xml'); ?></label></th>
                    <td><input type="file" id="xml_file" name="xml_file" value="" class="all-options" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e( 'Notification' , 'import-users-from-xml'); ?></th>
                    <td><fieldset>
                        <legend class="screen-reader-text"><span><?php _e( 'Notification' , 'import-users-from-xml'); ?></span></legend>
                        <label for="new_user_notification">
                            <input id="new_user_notification" name="new_user_notification" type="checkbox" value="1" />
                            Send to new users
                        </label>
                    </fieldset></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e( 'Password nag' , 'import-users-from-xml'); ?></th>
                    <td><fieldset>
                        <legend class="screen-reader-text"><span><?php _e( 'Password nag' , 'import-users-from-xml'); ?></span></legend>
                        <label for="password_nag">
                            <input id="password_nag" name="password_nag" type="checkbox" value="1" />
                            Show password nag on new users signon
                        </label>
                    </fieldset></td>
                </tr>
            </table>
            <p class="submit"><input type="submit" class="button-primary" value="<?php _e( 'Import' , 'import-users-from-xml'); ?>" /></p>
        </form>
    
    <?php endif; ?>
    
</div>