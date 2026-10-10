// ========================================
// MINTLY LANDING PAGE - JAVASCRIPT
// ========================================

// ========================================
// HEADER NAVIGATION
// ========================================

// Wait for DOM to be fully loaded
document.addEventListener(
	"DOMContentLoaded",
	function () {
		// Mobile Menu Toggle
		const mobileMenuToggle =
			document.querySelector(
				".mobile-menu-toggle"
			);
		const mobileNav = document.querySelector(
			".mobile-nav"
		);
		const header =
			document.querySelector(".header");
		const body = document.body;

		// Toggle mobile menu
		if (mobileMenuToggle && mobileNav) {
			mobileMenuToggle.addEventListener(
				"click",
				() => {
					const isExpanded =
						mobileMenuToggle.getAttribute(
							"aria-expanded"
						) === "true";

					mobileMenuToggle.setAttribute(
						"aria-expanded",
						!isExpanded
					);
					mobileNav.classList.toggle("active");
					body.style.overflow = !isExpanded
						? "hidden"
						: "";
				}
			);

			// Close mobile menu when clicking on a link
			const mobileNavLinks =
				document.querySelectorAll(
					".mobile-nav-link, .mobile-nav-btn"
				);
			mobileNavLinks.forEach((link) => {
				link.addEventListener("click", () => {
					mobileMenuToggle.setAttribute(
						"aria-expanded",
						"false"
					);
					mobileNav.classList.remove("active");
					body.style.overflow = "";
				});
			});

			// Close mobile menu on window resize
			window.addEventListener("resize", () => {
				if (window.innerWidth > 900) {
					mobileMenuToggle.setAttribute(
						"aria-expanded",
						"false"
					);
					mobileNav.classList.remove("active");
					body.style.overflow = "";
				}
			});
		}

		// Header scroll effect
		let lastScroll = 0;
		const scrollThreshold = 100;

		window.addEventListener("scroll", () => {
			const currentScroll = window.pageYOffset;

			// Add/remove scrolled class for styling
			if (currentScroll > 50) {
				header?.classList.add("scrolled");
			} else {
				header?.classList.remove("scrolled");
			}

			lastScroll = currentScroll;
		});

		// Smooth scroll for navigation links
		document
			.querySelectorAll('a[href^="#"]')
			.forEach((anchor) => {
				anchor.addEventListener(
					"click",
					function (e) {
						const href =
							this.getAttribute("href");

						// Only smooth scroll for valid anchors (not just "#")
						if (
							href &&
							href !== "#" &&
							href !== "#signin" &&
							href !== "#signup"
						) {
							const target =
								document.querySelector(href);
							if (target) {
								e.preventDefault();
								const headerOffset = 80;
								const elementPosition =
									target.getBoundingClientRect()
										.top;
								const offsetPosition =
									elementPosition +
									window.pageYOffset -
									headerOffset;

								window.scrollTo({
									top: offsetPosition,
									behavior: "smooth",
								});
							}
						}
					}
				);
			});
	}
);

// ========================================
// TESTIMONIAL CAROUSEL
// ========================================

let currentTestimonial = 0;

function showTestimonial(index) {
	const slides = document.querySelectorAll(
		".testimonial-slide"
	);
	const dots = document.querySelectorAll(".dot");

	// Return early if no slides exist
	if (slides.length === 0) return;

	// Validate index
	if (index >= slides.length) {
		currentTestimonial = 0;
	} else if (index < 0) {
		currentTestimonial = slides.length - 1;
	} else {
		currentTestimonial = index;
	}

	// Hide all slides
	slides.forEach((slide) =>
		slide.classList.remove("active")
	);
	dots.forEach((dot) =>
		dot.classList.remove("active")
	);

	// Show current slide
	if (slides[currentTestimonial]) {
		slides[currentTestimonial].classList.add(
			"active"
		);
	}
	if (dots[currentTestimonial]) {
		dots[currentTestimonial].classList.add(
			"active"
		);
	}
}

function nextTestimonial() {
	showTestimonial(currentTestimonial + 1);
}

function previousTestimonial() {
	showTestimonial(currentTestimonial - 1);
}

function goToTestimonial(index) {
	showTestimonial(index);
}

// Auto-rotate testimonials every 8 seconds
setInterval(() => {
	nextTestimonial();
}, 8000);

// ========================================
// LIVE CHAT WIDGET
// ========================================

let isChatOpen = false;

function toggleChat() {
	const chatContainer = document.querySelector(
		".chat-widget-container"
	);
	isChatOpen = !isChatOpen;

	if (isChatOpen) {
		chatContainer.classList.add("active");
		// Focus input when opening
		setTimeout(
			() =>
				document
					.getElementById("chatInput")
					.focus(),
			300
		);
	} else {
		chatContainer.classList.remove("active");
	}
}

function appendMessage(text, sender) {
	const chatBody =
		document.getElementById("chatBody");

	const messageDiv =
		document.createElement("div");
	messageDiv.className = `message ${sender}-message`;

	const contentDiv =
		document.createElement("div");
	contentDiv.className = "message-content";
	contentDiv.innerHTML = `<p>${text}</p>`;

	const timeSpan = document.createElement("span");
	timeSpan.className = "message-time";
	timeSpan.textContent = "Just now";

	messageDiv.appendChild(contentDiv);
	messageDiv.appendChild(timeSpan);

	chatBody.appendChild(messageDiv);
	chatBody.scrollTop = chatBody.scrollHeight;
}

function sendQuickReply(message) {
	appendMessage(message, "user");

	// Simulate bot response
	setTimeout(() => {
		const responses = {
			"Pricing questions":
				"Our basic plan is free forever! Premium starts at $9/mo with advanced AI features.",
			"How it works":
				"Connect your accounts, set your goals, and let our AI handle the rest. It's that simple.",
			"Talk to sales":
				"I can connect you with a sales representative. Please leave your email address.",
		};

		const response =
			responses[message] ||
			"Thanks for your message! A support agent will be with you shortly.";
		appendMessage(response, "bot");
	}, 1000);
}

function sendCustomMessage() {
	const input =
		document.getElementById("chatInput");
	const message = input.value.trim();

	if (message) {
		appendMessage(message, "user");
		input.value = "";

		setTimeout(() => {
			appendMessage(
				"Thanks for reaching out! We'll get back to you as soon as possible.",
				"bot"
			);
		}, 1000);
	}
}

// ========================================
// SMOOTH SCROLLING
// ========================================

document
	.querySelectorAll('a[href^="#"]')
	.forEach((anchor) => {
		anchor.addEventListener(
			"click",
			function (e) {
				const href = this.getAttribute("href");
				if (
					href !== "#" &&
					document.querySelector(href)
				) {
					e.preventDefault();
					document
						.querySelector(href)
						.scrollIntoView({
							behavior: "smooth",
						});
				}
			}
		);
	});

// ========================================
// FORM SUBMISSION
// ========================================

function handleSignup(event) {
	event.preventDefault();

	const form = event.target;
	const email = form.querySelector(
		'input[type="email"]'
	).value;

	if (!email) {
		alert("Please enter your email address.");
		return;
	}

	// Validate email
	const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
	if (!emailRegex.test(email)) {
		alert("Please enter a valid email address.");
		return;
	}

	// Here you would typically send the email to your backend
	console.log("Signup initiated for:", email);

	// Show success message
	const button = form.querySelector("button");
	const originalText = button.textContent;
	button.textContent = "✓ Check your email!";
	button.style.background = "#0D9488";

	// Reset form
	form.reset();

	// Reset button after 3 seconds
	setTimeout(() => {
		button.textContent = originalText;
		button.style.background = "";
	}, 3000);
}

function handleNewsletter(event) {
	event.preventDefault();
	const form = event.target;
	const email = form.querySelector(
		'input[type="email"]'
	).value;

	if (email) {
		const button = form.querySelector("button");
		const originalText = button.innerHTML;

		button.innerHTML = "Subscribed!";
		button.style.background = "#0D9488";

		setTimeout(() => {
			button.innerHTML = originalText;
			button.style.background = "";
			form.reset();
		}, 2000);
	}
}

// ========================================
// SCROLL ANIMATIONS - Professional Timing
// ========================================

const observerOptions = {
	threshold: 0.1,
	rootMargin: "0px 0px -60px 0px",
};

const observer = new IntersectionObserver(
	(entries) => {
		entries.forEach((entry) => {
			if (entry.isIntersecting) {
				// Add animate class to trigger CSS animations
				entry.target.classList.add("animate");
				observer.unobserve(entry.target);
			}
		});
	},
	observerOptions
);

// Observe elements for scroll animations
document
	.querySelectorAll(
		".how-it-works, .benefit-card, .step-item, .testimonial-card, .usp-highlight-card, .usp-features-column, .feature-item"
	)
	.forEach((el) => {
		observer.observe(el);
	});

// ========================================
// NAVBAR - NO NAVBAR AS PER DESIGN
// But keeping for potential future use
// ========================================

// Sticky header on scroll (optional enhancement)
window.addEventListener("scroll", () => {
	const hero = document.querySelector(".hero");
	const scrolled = window.scrollY > 100;

	if (scrolled) {
		// Can add scroll-triggered animations here
	}
});

// ========================================
// KEYBOARD NAVIGATION
// ========================================

document.addEventListener("keydown", (e) => {
	// Allow arrow keys for testimonial navigation
	if (e.key === "ArrowLeft") {
		previousTestimonial();
	} else if (e.key === "ArrowRight") {
		nextTestimonial();
	}

	// ESC to close chat
	if (e.key === "Escape") {
		const chatBody =
			document.getElementById("chatBody");
		if (isChatOpen) {
			toggleChat();
		}
	}
});

// ========================================
// MOBILE OPTIMIZATION
// ========================================

// Detect if user is on mobile
const isMobile = () => {
	return window.innerWidth <= 768;
};

// Adjust chat widget position on mobile - REMOVED (Handled by CSS)
// window.addEventListener("resize", () => { ... });

// ========================================
// PERFORMANCE OPTIMIZATION
// ========================================

// Lazy load images if using them
if ("IntersectionObserver" in window) {
	const imageObserver = new IntersectionObserver(
		(entries, observer) => {
			entries.forEach((entry) => {
				if (entry.isIntersecting) {
					const img = entry.target;
					if (img.dataset.src) {
						img.src = img.dataset.src;
						img.removeAttribute("data-src");
						observer.unobserve(img);
					}
				}
			});
		}
	);

	document
		.querySelectorAll("img[data-src]")
		.forEach((img) => imageObserver.observe(img));
}

// ========================================
// ANALYTICS TRACKING (Example)
// ========================================

function trackEvent(eventName, eventData = {}) {
	console.log(`Event: ${eventName}`, eventData);
	// Replace with your analytics service (GA, Mixpanel, etc.)
}

// Track CTA clicks
document
	.querySelectorAll(".cta-button")
	.forEach((btn) => {
		btn.addEventListener("click", () => {
			trackEvent("CTA_Click", {
				text: btn.textContent,
			});
		});
	});

// Track testimonial navigation
document
	.querySelectorAll(".carousel-btn, .dot")
	.forEach((btn) => {
		btn.addEventListener("click", () => {
			trackEvent("Testimonial_Navigation", {
				current: currentTestimonial,
			});
		});
	});

// ========================================
// PRICING TOGGLE
// ========================================

const billingToggle = document.getElementById(
	"billing-toggle"
);
const priceValues = document.querySelectorAll(
	".price-value"
);
const billingPeriods = document.querySelectorAll(
	".billing-period"
);

if (billingToggle) {
	billingToggle.addEventListener("change", () => {
		const isYearly = billingToggle.checked;

		priceValues.forEach((price) => {
			// Animate out
			price.style.opacity = "0";

			setTimeout(() => {
				if (isYearly) {
					price.textContent =
						price.dataset.yearly;
				} else {
					price.textContent =
						price.dataset.monthly;
				}
				// Animate in
				price.style.opacity = "1";
			}, 200);
		});

		billingPeriods.forEach((period) => {
			period.style.opacity = "0";
			setTimeout(() => {
				period.textContent = isYearly
					? "/mo (billed yearly)"
					: "/mo";
				period.style.opacity = "1";
			}, 200);
		});
	});
}

// ========================================
// FAQ ACCORDION
// ========================================

const accordionTriggers =
	document.querySelectorAll(".accordion-trigger");

accordionTriggers.forEach((trigger) => {
	trigger.addEventListener("click", () => {
		const isExpanded =
			trigger.getAttribute("aria-expanded") ===
			"true";
		const content = trigger.nextElementSibling;

		// Close all other items
		accordionTriggers.forEach((otherTrigger) => {
			if (otherTrigger !== trigger) {
				otherTrigger.setAttribute(
					"aria-expanded",
					"false"
				);
				otherTrigger.nextElementSibling.style.height =
					"0";
				otherTrigger.nextElementSibling.setAttribute(
					"aria-hidden",
					"true"
				);
			}
		});

		// Toggle current item
		trigger.setAttribute(
			"aria-expanded",
			!isExpanded
		);
		content.setAttribute(
			"aria-hidden",
			isExpanded
		);

		if (!isExpanded) {
			content.style.height =
				content.scrollHeight + "px";
		} else {
			content.style.height = "0";
		}
	});
});

// ========================================
// INITIALIZATION
// ========================================

document.addEventListener(
	"DOMContentLoaded",
	() => {
		console.log("Mintly Landing Page Loaded");

		// Initialize first testimonial
		showTestimonial(0);

		// Open chat by default (optional - comment out if not desired)
		// toggleChat();
	}
);

// ========================================
// VIDEO MODAL
// ========================================

const watchDemoBtn = document.getElementById(
	"watch-demo-btn"
);
const videoModal = document.getElementById(
	"video-modal"
);
const closeModalBtn = document.querySelector(
	".close-modal"
);
const demoVideo =
	document.getElementById("demo-video");

if (watchDemoBtn && videoModal && closeModalBtn) {
	watchDemoBtn.addEventListener("click", (e) => {
		e.preventDefault();
		videoModal.style.display = "flex";
		// Small delay to allow display:flex to apply before adding show class for transition
		setTimeout(() => {
			videoModal.classList.add("show");
			if (demoVideo) demoVideo.play();
		}, 10);
	});

	const closeVideoModal = () => {
		videoModal.classList.remove("show");
		setTimeout(() => {
			videoModal.style.display = "none";
			if (demoVideo) {
				demoVideo.pause();
				demoVideo.currentTime = 0;
			}
		}, 300); // Match transition duration
	};

	closeModalBtn.addEventListener(
		"click",
		closeVideoModal
	);

	// Close on click outside
	videoModal.addEventListener("click", (e) => {
		if (e.target === videoModal) {
			closeVideoModal();
		}
	});

	// Close on Escape key
	document.addEventListener("keydown", (e) => {
		if (
			e.key === "Escape" &&
			videoModal.classList.contains("show")
		) {
			closeVideoModal();
		}
	});
}

// ========================================
// SCROLL ANIMATIONS
// ========================================

document.addEventListener(
	"DOMContentLoaded",
	() => {
		// Elements to animate
		const animatedElements =
			document.querySelectorAll(
				".hero-text-wrapper, .hero-visual-wrapper, .benefits-header, .bento-card, .section-header-center, .process-step, .usp-header, .usp-card, .social-proof-header, .testimonial-card, .trust-logos, .pricing-card, .pricing-trust, .faq-header-col, .accordion-item, .signup-visual, .signup-form-container, .footer-cta-wrapper, .footer-col"
			); // Add base class
		animatedElements.forEach((el) => {
			el.classList.add("reveal-element");
		});

		// Intersection Observer
		const observerOptions = {
			root: null,
			rootMargin: "0px",
			threshold: 0.1,
		};

		const observer = new IntersectionObserver(
			(entries, observer) => {
				entries.forEach((entry) => {
					if (entry.isIntersecting) {
						// Add staggered delay based on index in container if possible,
						// or just simple reveal
						const target = entry.target;

						// Simple stagger logic for grids
						if (
							target.classList.contains(
								"bento-card"
							) ||
							target.classList.contains(
								"usp-card"
							) ||
							target.classList.contains(
								"process-step"
							) ||
							target.classList.contains(
								"testimonial-card"
							) ||
							target.classList.contains(
								"pricing-card"
							) ||
							target.classList.contains(
								"accordion-item"
							) ||
							target.classList.contains(
								"footer-col"
							)
						) {
							const siblings = Array.from(
								target.parentNode.children
							);
							const index =
								siblings.indexOf(target);
							target.style.transitionDelay = `${
								index * 0.1
							}s`;
						}

						target.classList.add("revealed");
						observer.unobserve(target);
					}
				});
			},
			observerOptions
		);

		animatedElements.forEach((el) => {
			observer.observe(el);
		});
	}
);
