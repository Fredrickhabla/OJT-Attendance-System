// autoLogout.js

let idleTimeLimit = 10000;
let idleTimer;
let hasLoggedOut = false;

function logoutUser() {
  if (hasLoggedOut) return;
  hasLoggedOut = true;
  window.location.href = "/ojtform/logout.php";
}

function resetIdleTimer() {
  clearTimeout(idleTimer);
  idleTimer = setTimeout(logoutUser, idleTimeLimit);
}

document.addEventListener("mousemove", resetIdleTimer);
document.addEventListener("keydown", resetIdleTimer);
document.addEventListener("click", resetIdleTimer);
document.addEventListener("scroll", resetIdleTimer);

resetIdleTimer();

window.addEventListener("beforeunload", function (event) {
  if (!hasLoggedOut && (!document.activeElement || document.activeElement.tagName === "BODY")) {
    hasLoggedOut = true;
    navigator.sendBeacon("/ojtform/logout.php");
  }
});
