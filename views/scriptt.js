document.addEventListener("DOMContentLoaded", () => {
  const signUpForm = document.querySelector(".sign-up");
  const signInForm = document.querySelector(".sign-in");
  const showSignIn = document.getElementById("show-signin");
  const showSignUp = document.getElementById("show-signup");

  showSignIn.addEventListener("click", () => {
    signUpForm.classList.add("hidden");
    signInForm.classList.remove("hidden");
  });

  showSignUp.addEventListener("click", () => {
    signInForm.classList.add("hidden");
    signUpForm.classList.remove("hidden");
  });
});
