<div id="umich-alerts--ealert" class="umich-alerts umich-ealert" data-uid="<?php echo md5( $alert['guid'] );?>">
    <div class="umich-alerts-wrap">
        <svg aria-hidden="true" focusable="false" data-icon="chevron-down" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="umich-alerts-icon umich-alerts-icon--chevron"><path fill="currentColor" d="M207.029 381.476L12.686 187.132c-9.373-9.373-9.373-24.569 0-33.941l22.667-22.667c9.357-9.357 24.522-9.375 33.901-.04L224 284.505l154.745-154.021c9.379-9.335 24.544-9.317 33.901.04l22.667 22.667c9.373 9.373 9.373 24.569 0 33.941L240.971 381.476c-9.373 9.372-24.569 9.372-33.942 0z" class=""></path></svg>
        <h2>
            <svg aria-hidden="true" focusable="false" data-icon="exclamation-triangle" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" class="umich-alerts-icon"><path fill="currentColor" d="M569.517 440.013C587.975 472.007 564.806 512 527.94 512H48.054c-36.937 0-59.999-40.055-41.577-71.987L246.423 23.985c18.467-32.009 64.72-31.951 83.154 0l239.94 416.028zM288 354c-25.405 0-46 20.595-46 46s20.595 46 46 46 46-20.595 46-46-20.595-46-46-46zm-43.673-165.346l7.418 136c.347 6.364 5.609 11.346 11.982 11.346h48.546c6.373 0 11.635-4.982 11.982-11.346l7.418-136c.375-6.874-5.098-12.654-11.982-12.654h-63.383c-6.884 0-12.356 5.78-11.981 12.654z" class=""></path></svg>
            <?php echo $alert['title'];?>
        </h2>
        <p><?php echo $alert['desc'];?></p>
        <a href="<?php echo $alert['link'];?>">Visit DPSS for updates</a>
    </div>
</div>
