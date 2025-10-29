
  const notifBtn = document.getElementById('notifBtn');
  const notifDropdown = document.getElementById('notifDropdown');
  const alertBtn = document.getElementById('alertBtn');
  const alertDropdown = document.getElementById('alertDropdown');

  if (notifBtn && notifDropdown) {
    notifBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      notifDropdown.classList.toggle('active');
      if (alertDropdown) {
        alertDropdown.classList.remove('active');
      }
    });
  }

  if (alertBtn && alertDropdown) {
    alertBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      alertDropdown.classList.toggle('active');
      if (notifDropdown) {
        notifDropdown.classList.remove('active');
      }
    });
  }

  document.addEventListener('click', () => {
    if (notifDropdown) {
      notifDropdown.classList.remove('active');
    }
    if (alertDropdown) {
      alertDropdown.classList.remove('active');
    }
  });

  // Handle notification/alert item clicks
  document.addEventListener('DOMContentLoaded', function() {
    const messageModalElement = document.getElementById('messageModal');
    if (!messageModalElement) return;
    
    let messageModal;
    try {
      messageModal = new bootstrap.Modal(messageModalElement);
    } catch (e) {
      console.error('Bootstrap Modal not available');
      return;
    }
    
    function initNotificationItemListeners() {
      const notificationItems = document.querySelectorAll('.notification-item[data-message]');
      
      notificationItems.forEach(item => {
        item.addEventListener('click', function(e) {
          e.stopPropagation();
          
          const message = this.getAttribute('data-message');
          const time = this.getAttribute('data-time');
          const type = this.getAttribute('data-type');
          const iconClass = this.getAttribute('data-icon');
          
          if (!message) return;
          
          // Update modal content
          const modalMessageEl = document.getElementById('modalMessage');
          const modalSubtitleEl = document.getElementById('modalSubtitle');
          const modalIconEl = document.getElementById('modalIcon');
          const modalTitleEl = document.getElementById('modalTitle');
          
          if (modalMessageEl) modalMessageEl.textContent = message;
          if (modalSubtitleEl) modalSubtitleEl.textContent = time;
          
          // Update icon
          if (modalIconEl) {
            modalIconEl.innerHTML = `<i class="fa-solid ${iconClass}"></i>`;
            const icon = modalIconEl.querySelector('i');
            if (icon) {
              if (type === 'alert') {
                icon.style.color = '#ff9800';
              } else {
                icon.style.color = '#007FFF';
              }
            }
          }
          
          // Update title based on type
          if (modalTitleEl) {
            modalTitleEl.textContent = type === 'alert' ? 'Alert' : 'Notification';
          }
          
          // Close dropdown
          if (notifDropdown) {
            notifDropdown.classList.remove('active');
          }
          if (alertDropdown) {
            alertDropdown.classList.remove('active');
          }
          
          // Show modal
          if (messageModal) {
            messageModal.show();
          }
        });
      });
    }
    
    // Initialize listeners
    initNotificationItemListeners();
  });
  

(function($){
	$(document).ready(function() {	
		// Scroll to Top
		jQuery('.scrolltotop').click(function(){
			jQuery('html').animate({'scrollTop' : '0px'}, 400);
			return false;
		});
		
		jQuery(window).scroll(function(){
			var upto = jQuery(window).scrollTop();
			if(upto > 500) {
				jQuery('.scrolltotop').fadeIn();
			} else {
				jQuery('.scrolltotop').fadeOut();
			}
		});

		// toggle pass eye
		$(".toggle-password").click(function() {
			$(this).toggleClass("fa-eye fa-eye-slash");
			input = $(this).parent().find("input");
			if (input.attr("type") == "password") {
				input.attr("type", "text");
			} else {
				input.attr("type", "password");
			}
		});

		// hamburger	
		$(".hamburger").click(function(){
			$(this).toggleClass("is-active");
		});
		
		// pie chart
		$('#walletpie').pieChart({
			barColor: '#767386',
			trackColor: '#2A2F49',
			lineCap: 'round',
			size: 500,
			lineWidth: 5,
			/*rotate: 90,*/
			onStep: function (from, to, percent) {
				$(this.element).find('.pie-value').text(Math.round(percent) + '%');
			}
		});
	});
})(jQuery);



function isGood(password) {
	var password_strength = document.getElementById("password-text");
  
	if (password.length === 0) {
	  password_strength.innerHTML = "";
	  return;
	}
  
	var hasLetter = /[a-zA-Z]/.test(password);
	var hasNumber = /[0-9]/.test(password);
  
	var strength = "";
  
	if (password.length < 6) {
	  strength = "<small class='progress-bar bg-danger' style='width: 30%'>Weak</small>";
	} else if (hasLetter && hasNumber) {
	  strength = "<small class='progress-bar bg-success' style='width: 100%'>Good</small>"; 
	} else {
	  strength = "<small class='progress-bar bg-warning' style='width: 60%'>Average</small>"; 
	}
  
	password_strength.innerHTML = strength;
  }
  


//   popup close aumatic script
document.addEventListener('shown.bs.modal', function (event) {
	const modal = document.getElementById('sendPopup3');
	if (event.target === modal) {
	  setTimeout(() => {
		modal.style.transition = 'opacity 0.5s ease';
		modal.style.opacity = '0';
  
		// Wait for transition to finish before hiding the modal
		setTimeout(() => {
		  const bsModal = bootstrap.Modal.getInstance(modal);
		  bsModal.hide();
		  modal.style.opacity = ''; // Reset opacity for future use
		}, 0);
	  }, 1000);
	}
  });


  // jQuery(function($) {
  //   $('.sideMenu_content ul li a').each(function() {
  //     const text = $(this).text().trim().toLowerCase();
  //     if (text.includes('settings')) {
  //       $(this).attr('href', './settings-main.html');
  //     }
  //   });
  // });

  //   jQuery(function($) {
  //   $('.dbrmh_right .dropdown-menu li a').each(function() {
  //     const text = $(this).text().trim().toLowerCase();
  //     if (text.includes('settings')) {
  //       $(this).attr('href', './settings-main.html');
  //     }
  //   });
  // });


  document.addEventListener("DOMContentLoaded", () => {
  const ignoreTags = ['SCRIPT', 'STYLE', 'TEXTAREA', 'INPUT'];

  function processNode(node) {
    if (node.nodeType === Node.TEXT_NODE) {
      if (/\d/.test(node.nodeValue)) {
        const replacedHTML = node.nodeValue.replace(/(\d+)/g, '<span class="font-secondary">$1</span>');
        const wrapper = document.createElement('span');
        wrapper.innerHTML = replacedHTML;
        node.parentNode.replaceChild(wrapper, node);
      }
    } else if (!ignoreTags.includes(node.nodeName)) {
      node.childNodes.forEach(processNode);
    }
  }

  processNode(document.body);
});


// gas price / limit script

  // Update slider fill background
  function updateSliderFill(slider) {
    let min = slider.min || 0;
    let max = slider.max || 100;
    let val = ((slider.value - min) / (max - min)) * 100;
    slider.style.setProperty('--val', val + '%');
  }

  // Gas price sync
  // const gasPriceInput = document.getElementById('gasPriceInput');
  // const gasPriceRange = document.getElementById('gasPriceRange');
  // gasPriceInput.addEventListener('input', () => {
  //   gasPriceRange.value = gasPriceInput.value;
  //   updateSliderFill(gasPriceRange);
  // });
  // gasPriceRange.addEventListener('input', () => {
  //   gasPriceInput.value = gasPriceRange.value;
  //   updateSliderFill(gasPriceRange);
  // });

  // Gas limit sync
  // const gasLimitInput = document.getElementById('gasLimitInput');
  // const gasLimitRange = document.getElementById('gasLimitRange');
  // gasLimitInput.addEventListener('input', () => {
  //   gasLimitRange.value = gasLimitInput.value;
  //   updateSliderFill(gasLimitRange);
  // });
  // gasLimitRange.addEventListener('input', () => {
  //   gasLimitInput.value = gasLimitRange.value;
  //   updateSliderFill(gasLimitRange);
  // });

  // Init fill colors on page load
  // document.querySelectorAll('.gas-range').forEach(slider => {
  //   updateSliderFill(slider);
  // });


// -----------------------------
// document.getElementById('setFee_btn').addEventListener('click', function (e) {
//     e.preventDefault(); // stop form submission

//     // Remove d-none from all elements with .gasPriceLimit_wrapper
//     document.querySelectorAll('.gasPriceLimit_wrapper').forEach(function (el) {
//         el.classList.remove('d-none');
//     });

//     // Change button text
//     this.innerText = 'SET DEFAULT';
// });