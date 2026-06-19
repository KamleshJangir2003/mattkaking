function saveScrollPosition() {
  localStorage.setItem('scrollPosition', window.scrollY);
}

window.addEventListener('load', function() {
  var scrollPosition = localStorage.getItem('scrollPosition');
  if (scrollPosition !== null) {
    window.scrollTo(0, parseInt(scrollPosition));
    localStorage.removeItem('scrollPosition');
  }
});

// VIP Popup
document.addEventListener('click', function(e) {
  if (e.target.closest('.open-premium-popup')) {
    e.preventDefault();
    document.getElementById('premiumPopup').classList.add('active');
  }
  if (e.target.classList.contains('premium-popup') || e.target.classList.contains('popup-close')) {
    document.getElementById('premiumPopup').classList.remove('active');
  }
});

document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    document.getElementById('premiumPopup').classList.remove('active');
  }
});
