function toggle() {
  let element = document.getElementById('comment_section')

  // element의 style이 block이면 none으로 변경 / none이면 block으로 변경 (토글 기능)
  if (element.style.display == "block")
    element.style.display = "none"
  else
    element.style.display = 'block';
}
