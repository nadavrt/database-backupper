$(function(){

  /*******************************/
  /**** Navigation and Views ****/
  /*****************************/

  $('.menu-box-tab').click(function(e){
      e.preventDefault();
      var availale_tabs = ['create','browse','import','settings','security'];
      var new_tab = $(this).attr('data-tab');
      var current_view = $('.view-box');
      if ( ( $.inArray(new_tab, availale_tabs) != -1 ) && ( $(current_view).attr('data-tab') !== new_tab ) )
      {
         $(current_view).attr('data-tab', new_tab);
         $('.info-box').hide();
         $('.sidebar-box').hide();
         $('.info-box[data-parent="' + new_tab + '"]').fadeIn(1000);
         $('.sidebar-box[data-parent="' + new_tab + '"]').fadeIn(1000);
      }
  });



  /**********************/
  /***** Create Tab *****/
  /**********************/

  $('#create-backup-form input[type="checkbox"]').change(function(){
    if ( ($('input[name="sqlFile"]:checked').length == 0) && ($('input[name="zipFile"]:checked').length == 0) ) $('#create-backup-form .backup-button').attr('disabled','disabled');
    else $('#create-backup-form .backup-button').removeAttr('disabled'); 
  });

  $('#create-backup-form').submit(function(e){
    e.preventDefault();
    if ( ($('input[name="sqlFile"]:checked').length == 0) && ($('input[name="zipFile"]:checked').length == 0) ) return;
    $('#create-backup-form .btn').attr('disabled','disabled');
    $('#download-files a.sql').addClass('hidden');
    $('#download-files a.zip').addClass('hidden');
    var loader = $('#download-files .loader');
    var statusReport = $('#download-files > p');
    $(statusReport).removeClass();
    $(statusReport).addClass('hidden');
    loader.fadeIn(1000);
    loader.next('.loader-message').fadeIn(1000);

    $.ajax({
      url: "functions/create_backup.php", // Url to which the request is send
      type: "POST",             // Type of request to be send, called as method
      data: new FormData(this), // Data sent to server, a set of key/value pairs (i.e. form fields and values)
      contentType: false,       // The content type used when sending data to the server.
      cache: false,             // To unable request pages to be cached
      processData:false,        // To send DOMDocument or non processed data file it is set to false
      success: function(response)   // A function to be called if request succeeds
      {
        loader.css('display','none');
        loader.next('.loader-message').css('display','none');
        $(statusReport).removeClass();
        if ( response !== false )
        {
          response = JSON.parse(response);
          if ( response.error )
          {
            $(statusReport).addClass('error');
            $(statusReport).text(response.error);
          }
          else if ( response.sql || response.zip )
          {
            $(statusReport).text('Backup created successfuly!');
            if ( response.sql ) $('#download-files a.sql').removeClass('hidden').attr('href',response.sql);
            if ( response.zip ) $('#download-files a.zip').removeClass('hidden').attr('href',response.zip);

            //Update the Browse & Export table to show the newly created table.
            $.post("functions/show_backups.php",
            {ajax:"true"},
            function(data)
            {
              $('#browse').html(data);
            });
          }

          $('#download-files').removeClass('hidden');
          $('#create-backup-form .btn').removeAttr('disabled');
        }
      }
    }); //End of Ajax call.

  }); //End of $('#create-backup-form').submit


  /**********************/
  /***** Browse Tab *****/
  /**********************/

  //Deleting a backup
  $('body').on('click', '.delete', function(e){
    e.preventDefault();
    var rowToDelete = $(this).closest('tr');
    var fileToDelete = $(this).data('name');
    if ( fileToDelete == '' ) return; //If this file is already being proccessed for deletion cancel the current request.

    fileToDelete = {fileName:fileToDelete};
    $(this).data('name',''); //Temporarily remove the file name from the list to prevent users from abusing the delete button.

    $.post('functions/delete_backup.php', fileToDelete, function(response) {
        if (response)
        {
          $(rowToDelete).fadeOut(1000, function(){
            $(rowToDelete).remove();  
          });
          
        }
    });
  });

  $('#filters div label').click( update_browse_filter );
  $('#filters div span').click( update_browse_filter );



  /**
  * filter the items being displayed based on the filter selected by the user.
  * @param node this The label or span selected by the user.
  * @return NULL
  *
  **/
  function update_browse_filter()
  {
    var filter =  $(this).parent();
    if ( $(filter).hasClass('checked') ) return;

    $('#filters div').removeClass('checked');
    $(filter).addClass('checked');

    switch( $(filter).data('filter') )
    {
      case 'desc':
        if ( $('#filters').data('order') != 'desc' )
        {
          change_browse_items_order();
          $('#filters').data('order','desc');
        }

        $('#browse tr').show();
        break; 

      case 'asc':
        if ( $('#filters').data('order') != 'asc' )
        {
          change_browse_items_order();
          $('#filters').data('order','asc');
        }
        $('#browse tr').show();
        break;

      case 'sql':
        $('#browse tr:not(:first-child)').hide();
        $('#browse tr.only-sql').show();
        break;

      case 'zip':
        $('#browse tr:not(:first-child)').hide();
        $('#browse tr.only-zip').show();
        break;
    }
  }


  /**
  * Flip the order in which items are listed in the Browse & Export tab.
  * @param NULL
  * @return NULL
  **/
  function change_browse_items_order()
  {
    var tableHead = '<tr>' + $('#browse table tbody > tr:first-child').html() + '</tr>';
    $('#browse table tbody > tr:first-child').remove();
    var tableBody = $('#browse table tbody > tr');
    var ascTableBody = '';
    var rowClass = '';
    
    $.each(tableBody, function(i, row){
      if ( $(row).hasClass('only-zip') ) rowClass = 'only-zip';
      if ( $(row).hasClass('only-sql') ) rowClass = 'only-sql';

      if ( rowClass == '') ascTableBody = '<tr>' + $(row).html() + '</tr>' + ascTableBody;
      else ascTableBody = '<tr class="' + rowClass + '">' + $(row).html() + '</tr>' + ascTableBody;
    });

    ascTableBody = tableHead + ascTableBody;
    $('#browse table tbody').html(ascTableBody);
  }


  /**********************/
  /***** Import Tab *****/
  /**********************/

  //Visualize the upload button and validate the file chose.
  $('#upload-file-button').click(function(){
    $('#import-backup-form input[type="file"]').trigger('click');
  });

  $('#import-backup-form input[type="file"]').change(function(){
    $('#file-name').text( $(this).val() );
    var file = $('#file-name').text();
    if ( (file.search('.sql') != -1) || (file.search('.zip') != -1) )  $('#import-backup-form .backup-button').removeAttr('disabled');
    else $('#import-backup-form .backup-button').attr('disabled','disabled');
  });


  $('#import-backup-form').submit(function(e){
    e.preventDefault();
    $(this).addClass('hidden');
    var loader = $('#import-status .loader');
    $('#import-status').removeClass('hidden');
    loader.fadeIn(1000);
    loader.next('.loader-message').fadeIn(1000);

    $.ajax({
      url: "functions/import_backup.php", // Url to which the request is send
      type: "POST",             // Type of request to be send, called as method
      data: new FormData(this), // Data sent to server, a set of key/value pairs (i.e. form fields and values)
      contentType: false,       // The content type used when sending data to the server.
      cache: false,             // To unable request pages to be cached
      processData:false,        // To send DOMDocument or non processed data file it is set to false
      success: function(response)   // A function to be called if request succeeds
      {
        loader.css('display','none');
        loader.next('.loader-message').css('display','none');
        var statusReport = $('#import-status > p');
        statusReport.removeClass();
        if ( response !== false )
        {
          response = JSON.parse(response);
          if ( response.success || response.error )
          {
            if ( response.success ) 
            {
              statusReport.text(response.success);
              statusReport.addClass('success');
            }
            else
            {
              statusReport.text(response.error);
              $('.refresh-button').removeClass('hidden');
              statusReport.addClass('error');
            }
          }
        }
        else
        {
          statusReport.text('Failed to import backup. Please refresh the page and try again.');
          statusReport.addClass('error');
        }

        $('.refresh-button').removeClass('hidden');
      }
    }); //End of Ajax call.
  }); // End of $('#import-backup-form').submit

  $('.refresh-button').click(function(e){
    e.preventDefault();
    $(this).addClass('hidden');
    $('#import-status').addClass('hidden');
    $('#import-status > p').text('').removeClass();
    $('#import-backup-form').removeClass('hidden');
  });


  /**********************/
  /**** Settings Tab ****/
  /**********************/

  $('#locker').click(function(){
    var locker = $(this);
    if ( $(locker).hasClass('fa-lock') )
    {
      $(locker).removeClass('fa-lock').addClass('fa-unlock')
      $('#settings-form').fadeIn().prepend('<input type="hidden" name="changeCredentials" value="true" />');
    }
    else
    {
      $(locker).removeClass('fa-unlock').addClass('fa-lock')
      $('#settings-form').fadeOut();
      $('input[name="changeCredentials"]').remove();
    }
  });

  $('.theme-block').click(function(){
    var theme = $(this).data('theme');
    $('#theme').remove();
    $('#settings-form').prepend('<input type="hidden" id="theme" name="theme" value="' + theme + '">');
    $('#settings').removeClass().addClass(theme);
  });

  $('#save-settings').click(function(){
      $('#settings-form').trigger('submit');
  });

  $('#settings-form').submit(function(){
    var submit = confirm('Saving the setting will requires the program to restart. Continue?');
    if ( !submit ) return false;
  });


  /**********************/
  /**** Security Tab ****/
  /**********************/

  $('#htaccessCheck').click(function(){
      $(this).toggleClass('checked');
      if ( $(this).hasClass('checked') )
      {
        $('#enableHtaccess').val('true');
        $('.credential-block').fadeIn();
      }
      else
      {
        $('#enableHtaccess').val('false');
        $('.credential-block').fadeOut();
      } 
  });

  $('#security-form').submit(function(){
      var submit = confirm('This step will alter you .htaccess file and restart the program. Continue?');
      if ( !submit ) return false;
  });

});