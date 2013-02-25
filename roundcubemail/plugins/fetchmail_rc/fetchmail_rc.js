if (window.rcmail) {
  rcmail.addEventListener('init', function(evt) {
      var tab = $('<span>').attr('id', 'settingstabpluginfetchmailrc').addClass('tablink filter'),      
      button = $('<a>').attr('href', rcmail.env.comm_path+'&_action=plugin.fetchmail_rc')
          .attr('title', rcmail.gettext('fetchmail_rc.manageaccounts'))
          .html(rcmail.gettext('fetchmail_rc.accounts'))
          .appendTo(tab);
      // add tab
      rcmail.add_element(tab, 'tabs');
  });
}


