$("#selectCategory").change(function() {
  var template = $(this).find('option:selected').data('template');
  $('#textareaMessage').html(template);
});