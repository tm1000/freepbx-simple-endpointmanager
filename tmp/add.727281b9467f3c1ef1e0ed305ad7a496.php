<?php if(!class_exists('raintpl')){exit;}?>        <div id="box">
            <div id="message"></div>
            <form method="post" id="add">
                  Mac Address: <input autofocus="TRUE" type="text" name="mac" id="mac" class="required mac"/><br />
                  Device: 
                  <select name="device">
                      <?php $counter1=-1; if( isset($devices) && is_array($devices) && sizeof($devices) ) foreach( $devices as $key1 => $value1 ){ $counter1++; ?>

                      <option value="<?php echo $value1["id"];?>" <?php if( isset($value1["selected"]) ){ ?>selected<?php } ?>><?php echo $value1["model"];?></option>
                      <?php } ?>

                  </select><br />
                  Extention Number: <input type="text" name="ext" id="ext" class="required number ext"/><br />
                  User Name: <input type="text" name="displayname" id="displayname"/><br />
                  <input type="hidden" name="type" value="add"/>
                  <input type="submit" value="Save" />
            </form>
            <div id="status">
                Status</br>
            </div>
        </div>
        <script>
            $('.input').keypress(function(e) {
                if(e.which == 13) {
                    jQuery(this).blur();
                    jQuery('#submit').focus().click();
                }
            });
        </script>