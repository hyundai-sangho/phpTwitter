$(document).ready(function () {
  // "계정이 없다면 회원 가입" 부분을 클릭하면
  // 1. 로그인 양식을 숨기고
  // 2. 계정 등록 양식 표시
  $('#signUp').click(function () {
    $('#first').slideUp('slow', function () {
      $('#second').slideDown('slow');
    });
  });

  // "계정이 있다면 로그인" 부분을 클릭하면
  // 1. 회원 등록 양식을 숨기고
  // 2. 로그인 양식 표시
  $('#signIn').click(function () {
    $('#second').slideUp('slow', function () {
      $('#first').slideDown('slow');
    });
  });
});
