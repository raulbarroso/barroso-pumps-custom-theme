// Menu
jQuery(document).ready(function() {

  // Variables
  var jQuerycodeSnippets = jQuery('.code-example-body'),
      jQuerynav = jQuery('.storefront-primary-navigation'),
      jQuerybody = jQuery('body'),
      jQuerywindow = jQuery(window),
      jQuerypopoverLink = jQuery('[data-popover]'),
      navOffsetTop = jQuerynav.offset().top,
      jQuerydocument = jQuery(document),
      entityMap = {
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': '&quot;',
        "'": '&#39;',
        "/": '&#x2F;'
      }

  function init() {
    jQuerywindow.on('scroll', onScroll);
    jQuerywindow.on('resize', resize);
  }

  function resize() {
    jQuerybody.removeClass('has-docked-nav')
    navOffsetTop = jQuerynav.offset().top
    onScroll()
  }

  function onScroll() {
    if(navOffsetTop < jQuerywindow.scrollTop() && !jQuerybody.hasClass('has-docked-nav')) {
      jQuerybody.addClass('has-docked-nav')
    }
    if(navOffsetTop > jQuerywindow.scrollTop() && jQuerybody.hasClass('has-docked-nav')) {
      jQuerybody.removeClass('has-docked-nav')
    }
  }

  init();

});

// Frontpage Look Up button

function lookUp(){
  var product = document.getElementById("lookup-select-pa_product").value;
  if (product == "Pumps"){
    // Get values in correct order
    var order = ["installation", "child-category", "horsepower", "discharge-size", "max-flow"];
    var arr = new Array(order.length);
    var sels = document.getElementsByClassName("sel-pumps")
    for (i = 0; i < sels.length; i++){
      var name = sels[i].id.split("pa_")[1];
      var val = sels[i].value;
      var position = order.indexOf(name);
      if (position != -1 && val != ""){
        arr[position] = name + "-" + val;
      }
    }
    // Create url
    var url = ["product-category", "all-pumps"]
    for (i = 0; i < arr.length; i++){
      if (arr[i] != undefined){
        url.push(arr[i])
      }
    }
    window.location.href += url.join("/")
  } else if (product == "Motors"){
    console.log("Motors")
  }
}

// Filter Everything PRO: 
// Remove sidebar and expand content if no filter
if (document.body.classList.contains("left-sidebar")){
  var sidebar = document.getElementById("secondary");
  if (sidebar != null && sidebar.innerHTML.length < 1000){
    sidebar.style.display = 'none';
    var primary = document.getElementById("primary");
    primary.style.margin = 'auto';
    primary.style.float = 'none';
  }
}

// Remove installation, application, and category if selected
var url = window.location.href.split("/");
var index = url.indexOf("product-category");
if (url[index + 1].includes("pumps")){
  // Dropdown
  var removeCats = ["application", "child-category"];
  for (i = 0; i < removeCats.length; i++){
    var filter = document.getElementsByClassName("wpc-filter-pa_" + removeCats[i])[0];
    if (filter == undefined){
      continue;
    }
    var select = filter.getElementsByTagName("SELECT")[0];
    var options = filter.getElementsByTagName("OPTION");
    if (options.length <= 2 && select[0].value == 0){
      filter.style.display = 'none';
    }
  }
  // Radio
  var removeCats = ["installation"];
  for (i = 0; i < removeCats.length; i++){
    var filter = document.getElementsByClassName("wpc-filter-pa_" + removeCats[i])[0];
    if (filter == undefined){
      continue;
    }
    var radio = filter.getElementsByTagName("INPUT");
    if (radio.length <= 1 && radio[0].checked != true){
      filter.style.display = 'none';
    }
  }
}

// Make first select invisible
var dropdowns = document.getElementsByClassName("wpc-filter-layout-dropdown");
if (dropdowns != undefined){
  for (i = 0; i < dropdowns.length; i++){
    dropdowns[i].getElementsByTagName("OPTION")[0].innerHTML = "";
  }
}


// Handheld menu: remove corresponding My Account link
var nav = document.getElementById('menu-handheld-menu').getElementsByTagName("li");
var myaccount;
var login;
for (i = 0; i < nav.length; i++){
  if (nav[i].firstElementChild.innerHTML == "My account" || nav[i].firstElementChild.innerHTML == "Mi cuenta"){
    myaccount = nav[i];
  } else if (nav[i].firstElementChild.innerHTML == "Register or Log In" || nav[i].firstElementChild.innerHTML == "Regístrate o Entra"){
    login = nav[i];
  }
}
if (document.body.classList.contains("logged-in")){
  login.style.display = 'none';
} else {
  myaccount.style.display = 'none';
}

// Handheld menu: remove corresponding lang selector

var selectors = document.getElementsByClassName('trp-language-switcher-container');
var englishSelector = selectors[0];
var spanishSelector = selectors[1];
if (document.body.classList.contains("translatepress-en_US")){
  englishSelector.style.display = 'none';
} else {
  spanishSelector.style.display = 'none';
}

// Handheld footer: Add class

var handheldFooter = document.getElementsByClassName('storefront-handheld-footer-bar')[0];
var searchArr = document.getElementsByClassName('search');
var searchButton = searchArr[searchArr.length - 1];
searchButton.onclick = addHandheldFooterClass;

function addHandheldFooterClass() {
  handheldFooter.classList.toggle("active");
}

// Request quote button, can be rent or sale
if (typeof formType !== 'undefined') {
  if (formType == "rent"){
    var elm = document.getElementById("quote-rent");
    var formTitle = "Rent";
    var dateInput = "<div class='input-col1'>\
                      <label for='date'>Date of Rental<abbr class='required'>*</abbr></label>\
                      <input name='date' type='date' class='input-text' id='date'>\
                    </div>\
                    <div class='input-col2'>\
                      <label for='duration'>Duration<abbr class='required'>*</abbr></label>\
                      <input name='amount' type='number' class='input-text' value='1' id='duration-num'>\
                      <select name='duration' id='duration'>\
                        <option value='Day'>Day(s)</option>\
                        <option value='Week'>Week(s)</option>\
                        <option value='Month'>Month(s)</option>\
                      </select>\
                    </div>";
  } else if (formType == "sale"){
    var elm = document.getElementById("quote-sale");
    var formTitle = "Request";
    var dateInput = "";
  }
}

if (elm){
  var formValid = true;
  var formId = "quote-" + formType;
  var original = elm.innerHTML;
  var productTitle = document.getElementsByClassName("product_title")[0].innerHTML;
  var form = "<div id='close-form'></div>\
    <form name='Quote' id='qform' autocomplete='on' action='" + postUrl + "' method='post'>\
      <p id='form-title'>" + formTitle + " Form</p><br>\
      <span id='error'>Make sure to fill out the required fields</span>\
      <div class='input-col1'>\
        <label for='firstname'>First Name<abbr class='required'>*</abbr></label>\
        <input name='firstname' type='text' class='input-text' id='name' maxlength='40'>\
      </div>\
      <div class='input-col2'>\
        <label for='surname'>Last Name<abbr class='required'>*</abbr></label>\
        <input name='surname' type='text' class='input-text' id='surname' maxlength='40'>\
      </div>\
      <label for='company'>Company</div></label>\
      <input name='company' type='text' class='optional input-text' id='company' maxlength='40'>\
      <label for='email'>Email<abbr class='required'>*</abbr></label>\
      <input name='email' type='email' class='input-text' id='email' maxlength='40'>\
      <label for='phone'>Phone<abbr class='required'>*</abbr></label>\
      <input name='phone' type='tel' class='input-text' id='phone' maxlength='12'>" + dateInput + "\
      <div class='input-col1'>\
        <p>Product</p>\
        <p id='form-product-title'>" + productTitle + "</p>\
      </div>\
      <div class='input-col2'>\
        <label for='qty'>Quantity<abbr class='required'>*</abbr></label>\
        <input name='qty' type='number' class='input-text' value='1' id='qty-num'>\
      </div>\
      <br>\
      <input type='text' name='tel' value='0' style='display:none !important' tabindex='-1' autocomplete='off'>\
      <input id='submit-form' name='submit' class='button' type='submit' value ='" + formTitle + " this product' onclick='submitForm(this.form)'>\
  </form>"
  elm.addEventListener("click", toggleForm);
}

function toggleForm() {  // opens and closes form in quote button
  elm.classList.toggle("quote-form");
  var hasClass = document.getElementsByClassName("quote-form");
  if (hasClass[0]){ // open
    elm.innerHTML = "";
    window.setTimeout(function(){
      elm.innerHTML = form;
      document.getElementById("close-form").addEventListener("click", toggleForm);
      setUserData();
      if (formType == "rent"){
        setMinDate();
      }
    }, 100);
    elm.removeEventListener("click", toggleForm);
  } else { // close
    elm.innerHTML = original;
    document.getElementById(formId).classList.remove("has-error");
    window.setTimeout(function(){
      elm.addEventListener("click", toggleForm)
    }, 1);
  }
}

function setMinDate() { // sets today as date and as min value
  var today = new Date();
  var dd = today.getDate();
  var mm = today.getMonth()+1; //January is 0!
  var yyyy = today.getFullYear();
   if(dd<10){
          dd = '0' + dd
      }
      if(mm<10){
          mm = '0' + mm
      }

  today = yyyy + '-' + mm + '-' + dd;
  document.getElementById("date").setAttribute("min", today);
  document.getElementById("date").setAttribute("value", today);
}

function setUserData() { // puts user data sent from functions.php
  if(uName){
    document.getElementById("name").setAttribute("value", uName);
  }
  if (uSurname){
    document.getElementById("surname").setAttribute("value", uSurname);
  }
  if (uEmail){
    document.getElementById("email").setAttribute("value", uEmail);
  }
}

function submitForm(form) { // validates form after submit
  formValid = true;
  // validate name
  valInput("name", /^[A-Za-zÀ-ÖØ-öø-ÿ\s]+$/g)
  // validate surname
  valInput("surname", /^[A-Za-zÀ-ÖØ-öø-ÿ\s]+$/g)
  // validate company
  if (document.getElementById("company").value != ''){
    valInput("company", /^[!-z\s]+$/g);
  }
  // validate email
  valInput("email", /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/g);
  // validate phone
  valInput("phone", /^\+?(\(?[0-9]{3}\)?|[0-9]{3})[-\.\s]?[0-9]{3}[-\.\s]?[0-9]{4}$/g);
  if (formType == "rent"){
    // validate date
    valInput("date", /^[\d\\\/\.-]+$/g);
    // validate duration-num
    valInput("duration-num", /^[\d]+$/g);
    // validate duration
    valInput("duration", /^[a-zA-Zí]+$/g);
  }
  // validate qty-num
  valInput("qty-num", /^[\d]+$/g);
  if (!formValid){
    event.preventDefault();
    document.getElementById(formId).classList.add("has-error");
  }
  // else form is sent as POST to same page
}

function valInput(input, regex){
  var input = document.getElementById(input);
  if (!input.value.match(regex)){
    formValid = false;
    input.classList.add("error");
  } else {
    input.classList.remove("error");
  }
}

// Calculate Shipping WIP
//document.getElementById("calc_shipping_country").addEventListener("change", calcShippingPuertoRico);

function calcShippingPuertoRico(){
  var calcCountry = document.getElementById("calc_shipping_country").value;
  if (calcCountry == "PR");
  document.getElementById("calc_shipping_state").value = "Test";
}
