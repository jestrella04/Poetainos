"use strict";

import * as bootstrap from "bootstrap";
import * as fx from "./functions";
import * as push from "./push";
import "@pwabuilder/pwaupdate";
import "@pwabuilder/pwainstall";
import autoGrow from "@ivanhanak_com/js-textarea-autogrow";
import Tribute from "tributejs";
import Tags from "bootstrap5-tags";
import Masonry from "masonry-layout";
import imagesLoaded from "imagesloaded";
import InfiniteScroll from "infinite-scroll";
import axios from "axios";

// PWA Builder goodies
const installComponent = document.createElement("pwa-install");
const updateComponent = document.createElement("pwa-update");
const isInstalled = installComponent.getInstalledStatus();

// Scroll pos
let oldScrollPos = window.scrollY;

//document.querySelector('footer').appendChild(installComponent);
document.querySelector("footer").appendChild(updateComponent);

// Customizing displayed messages
installComponent.manifestpath = "/manifest.json";
installComponent.explainer =
    "Puedes instalar esta aplicacion web en tu dispositivo y disfrutar de una experiencia nativa.";
installComponent.featuresheader = "Funcionalidades Principales";
installComponent.descriptionheader = "Descripción";
installComponent.installbuttontext = "Instalar";
installComponent.cancelbuttontext = "Cancelar";
installComponent.iosinstallinfotext =
    'Presiona el botón compartir y después "Añadir a la pantalla principal"';
updateComponent.updatemessage = "Hay una actualización disponible";

// Wait for the page to be fully loaded
window.addEventListener("load", () => {
    // Display the flash messages toast
    fx.showToast({
        selector: "#toast-flash",
    });

    // Dispatch change event to enforce the app loads
    // alternative categories when editing a writing
    let mainCategory = document.querySelector("#main-category");
    let subCategories = document.querySelector("#categories");
    let tagsInstance = Tags.getInstance(subCategories);

    if (!fx.isNil(mainCategory) && !fx.isNilOrEmpty(mainCategory.value)) {
        subCategories.disabled = false;
        fx.subCategoriesUpdate(subCategories, mainCategory);
        tagsInstance.resetSuggestions();
        tagsInstance.resetState();
    }
});

// Wait for the DOM to be ready
document.addEventListener("DOMContentLoaded", () => {
    // Check for user session
    let userToken = document.querySelector('meta[name="user-token"]');

    if (!fx.isNil(userToken)) {
        userToken = atob(userToken.content);
    }

    // Initialize Tags
    Tags.init(".tags-select", {
        allowClear: true,
        suggestionsThreshold: 0,
    });

    // Initialize tooltips
    [].slice
        .call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        .map((tooltipTriggerEl) => {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

    // Initialize popovers
    [].slice
        .call(document.querySelectorAll('[data-bs-toggle="popover"]'))
        .map((popoverTriggerEl) => {
            let helpText =
                popoverTriggerEl.parentElement.querySelector(".help").innerHTML;
            let options = {
                delay: 100,
                trigger: "focus",
                placement: "top",
                content: helpText,
                html: true,
            };

            return new bootstrap.Popover(popoverTriggerEl, options);
        });

    // Initialize Masonry
    document.querySelectorAll(".masonry").forEach((masonryElement) => {
        new Masonry(masonryElement);
    });

    // Enable infinite scroll
    InfiniteScroll.imagesLoaded = imagesLoaded;
    document
        .querySelectorAll(".infinite-scroll")
        .forEach((infiniteScrollElement) => {
            // Check if there's a pagination element
            let mainPagination = document.querySelector(".main-pagination");
            let notificationsPagination = document.querySelector(
                "#notifications-pagination"
            );

            if (!fx.isNilOrEmpty(mainPagination)) {
                new InfiniteScroll(infiniteScrollElement, {
                    path: ".pagination-next",
                    append: ".entry-container",
                    button: "#pagination-load-more",
                    hideNav: ".main-pagination",
                    scrollThreshold: false,
                    outlayer: Masonry.data(infiniteScrollElement),
                    status: ".loading-content",
                });
            }

            if (!fx.isNilOrEmpty(notificationsPagination)) {
                new InfiniteScroll(infiniteScrollElement, {
                    path: ".page-link[rel='next']",
                    append: ".user-notification",
                    button: "#pagination-load-more",
                    hideNav: "#notifications-pagination",
                    scrollThreshold: false,
                    status: ".loading-content",
                });
            }
        });

    // Disable tracking if the opt-out cookie exists.
    if (!fx.isNil(window.analytics_id)) {
        let disableStr = `ga-disable-${window.analytics_id}`;

        if (document.cookie.indexOf(`${disableStr}=true`) > -1) {
            window[disableStr] = true;
        }
    }

    // Set what modal element should be displayed
    let modalForm = document.querySelector(".form-wrapper");

    // Working with admin modal forms
    if (!fx.isNil(modalForm) && modalForm.matches(".modal")) {
        let targetForm = modalForm.querySelector("form");
        let modalTitleElement = modalForm.querySelector(".modal-title");

        // Change title if updating
        modalForm.addEventListener("show.bs.modal", (event) => {
            let relatedTarget = event.relatedTarget;

            if (fx.isNil(relatedTarget)) {
                modalTitleElement
                    .querySelector(".create")
                    .classList.add("d-none");
                modalTitleElement
                    .querySelector(".update")
                    .classList.remove("d-none");
            }
        });

        // Do some cleaning when the modal is closed
        modalForm.addEventListener("hidden.bs.modal", () => {
            // Reset title
            modalTitleElement
                .querySelector(".create")
                .classList.remove("d-none");
            modalTitleElement.querySelector(".update").classList.add("d-none");

            // Reset form to default empty values
            fx.resetAdminFormCreate(targetForm);

            // Hide all the error helpers
            targetForm.querySelectorAll(".text-danger").forEach((helper) => {
                helper.innerHTML = "";
                helper.classList.add("d-none");
            });
        });
    }

    // Push switcher
    let pushSwitch = document.querySelector("#enable-push-notifications");

    // Managing push subscriptions
    navigator.serviceWorker.ready.then((registration) => {
        registration.pushManager
            .getSubscription()
            .then((subscription) => {
                // Keep subscription in sync with server
                if (subscription) {
                    push.subscribe();
                }

                // Check the push switcher
                if (subscription && !fx.isNil(pushSwitch)) {
                    pushSwitch.checked = true;
                }

                // Uncheck the push switcher
                if (!subscription && !fx.isNil(pushSwitch)) {
                    pushSwitch.checked = false;
                }
            })
            .catch((e) => {
                console.log("Error thrown checking subscription status.", e);
            });
    });

    // Email switcher
    let emailSwitch = document.querySelector("#enable-email-notifications");

    // Check notifications status
    if (!fx.isNil(emailSwitch)) {
        axios
            .post("notifications/status")
            .then((response) => {
                if ("on" === response.data.email) {
                    emailSwitch.checked = true;
                } else {
                    emailSwitch.checked = false;
                }
            })
            .catch((error) => {
                //
            });
    }

    // Check if app is installed
    if (isInstalled) {
        // Hide the footer
        document.querySelector("footer").classList.add("d-none");
    }

    // Animate logo on login page
    let loginLogo = document.querySelector(".login-logo");

    if (!fx.isNilOrEmpty(loginLogo)) {
        fx.animateCSS(loginLogo, "backInUp");
    }

    // Listen to the toast show event and act accordingly
    document.querySelector(".toast").addEventListener(
        "show.bs.toast",
        (event) => {
            event.target.closest(".toast-wrapper").classList.add("show");
        },
        false
    );

    // Listen to the toast hidden event and act accordingly
    document.querySelector(".toast").addEventListener(
        "hidden.bs.toast",
        (event) => {
            event.target.closest(".toast-wrapper").classList.remove("show");
        },
        false
    );

    // Reset toast look/info when closed
    document.querySelector("#toast-reuse").addEventListener(
        "hidden.bs.toast",
        (event) => {
            // Set default look & feel
            event.target.classList.remove("success", "danger");

            // Remove last message
            event.target.querySelector(".toast-body").innerHTML = "";
        },
        false
    );

    // Listen for new user notification events coming from the server
    if (!fx.isNil(userToken)) {
        Echo.private(`notifications.${userToken}`).listen(
            "NotificationEvent",
            (payload) => {
                document.querySelectorAll(".unread-count").forEach((badge) => {
                    badge.querySelector(".count").innerHTML =
                        payload.notifications.unread;
                    badge.classList.remove("d-none");
                    fx.animateCSS(badge, "heartBeat");
                });
            }
        );
    }

    // Listen to the on click event on the page and act accordingly
    document.addEventListener("click", (event) => {
        let element = event.target;

        // Bubble up click event on certain elements
        let bubble =
            element.closest(
                "a, label, button, .btn, .badge, .avatar-chooser"
            ) || false;

        if (bubble) {
            element = bubble;
        }

        // Scroll to the main nav
        if (element.matches(".jump-to-top")) {
            event.preventDefault();
            document
                .querySelector("body")
                .scrollIntoView({ behavior: "smooth" });
        }

        // Dynamically load comments for a writing
        if (!fx.isNil(element.parentElement)) {
            if (element.parentElement.matches["#load-more"]) {
                let url = element.dataset.whHref;

                fx.loadComments(url);
            }
        }

        // Show/hide the comment reply form
        if (element.matches(".badge-reply")) {
            let targetForm = document.querySelector(element.dataset.whTarget);
            targetForm.classList.toggle("d-none");
            targetForm.querySelector(".form-control").focus();

            document
                .querySelectorAll(".comment-form")
                .forEach((commentForm) => {
                    if (
                        !commentForm.matches("#post-comment-form") &&
                        !commentForm.matches("#" + targetForm.id)
                    ) {
                        commentForm.classList.add("d-none");
                    }
                });
        }

        // Trigger the cover chooser
        if (element.matches("#cover-chooser")) {
            event.preventDefault();
            let fileChooser = document.querySelector(element.dataset.whTarget);

            fileChooser.click();
        }

        // Trigger the avatar chooser
        if (element.matches(".avatar-chooser")) {
            event.preventDefault();
            let fileChooser = document.querySelector(element.dataset.whTarget);

            fileChooser.click();
        }

        // Share button (if Share API is supported)
        if (element.matches(".sharer")) {
            event.preventDefault();

            if (navigator.share) {
                navigator.share({
                    title: element.dataset.whTitle,
                    url: element.dataset.whUrl,
                });
            } else {
                document
                    .querySelectorAll(".modal-sharer")
                    .forEach((e) => e.remove());

                axios
                    .get(element.href)
                    .then((response) => {
                        document.body.insertAdjacentHTML(
                            "beforeend",
                            response.data
                        );
                    })
                    .catch((error) => {
                        //
                    })
                    .then(() => {
                        let sharerModalElement =
                            document.querySelector(".modal-sharer");
                        let sharerModal = new bootstrap.Modal(
                            sharerModalElement
                        );
                        sharerModal.show();
                    });
            }
        }

        // Counters
        if (element.closest(".stats")) {
            // Managing likeables
            if (element.matches(".likeable") || element.matches(".liked")) {
                event.preventDefault();

                if (
                    "whTargetStore" in element.dataset &&
                    "whTargetDelete" in element.dataset
                ) {
                    let url = element.matches(".liked")
                        ? element.dataset.whTargetDelete
                        : element.dataset.whTargetStore;
                    let method = element.matches(".liked") ? "delete" : "post";
                    let params = new FormData();
                    let count;

                    params.append("_method", method);

                    axios
                        .post(url, params)
                        .then((response) => {
                            count = response.data.count;

                            if ("post" == method) {
                                element.classList.add("liked");
                            } else {
                                element.classList.remove("liked");
                            }
                        })
                        .catch((error) => {
                            if (403 === error.response.status) {
                                location.href = element.dataset.whTargetGuest;
                            }
                        })
                        .then(() => {
                            if (!fx.isNilOrEmpty(count)) {
                                element.querySelector(".counter").textContent =
                                    count;
                                fx.animateCSS(
                                    element.querySelector("i"),
                                    "heartBeat"
                                );
                                fx.animateCSS(
                                    element.querySelector(".counter"),
                                    "fadeIn"
                                );
                            }
                        });
                }
            }

            // Managing shelf
            if (element.matches(".shelf") || element.matches(".shelved")) {
                event.preventDefault();

                if (
                    "whTargetStore" in element.dataset &&
                    "whTargetDelete" in element.dataset
                ) {
                    let url = element.matches(".shelved")
                        ? element.dataset.whTargetDelete
                        : element.dataset.whTargetStore;
                    let method = element.matches(".shelved")
                        ? "delete"
                        : "post";
                    let params = new FormData();
                    let count;

                    params.append("_method", method);

                    axios
                        .post(url, params)
                        .then((response) => {
                            count = response.data.count;

                            if ("post" == method) {
                                element.classList.add("shelved");
                            } else {
                                element.classList.remove("shelved");
                            }
                        })
                        .catch((error) => {
                            if (403 === error.response.status) {
                                location.href = element.dataset.whTargetGuest;
                            }
                        })
                        .then(() => {
                            if (!fx.isNilOrEmpty(count)) {
                                element.querySelector(".counter").textContent =
                                    count;
                                fx.animateCSS(
                                    element.querySelector("i"),
                                    "heartBeat"
                                );
                                fx.animateCSS(
                                    element.querySelector(".counter"),
                                    "fadeIn"
                                );
                            }
                        });
                }
            }
        }

        // Share links
        if (element.matches(".share-link")) {
            event.preventDefault();

            let url = element.href;

            if (element.matches(".copy-to-clipboard-link")) {
                navigator.clipboard.writeText(url);
            } else {
                let params =
                    "scrollbars=no,resizable=no,status=no,location=no,toolbar=no,menubar=no,width=500,height=500,left=100,top=100";

                open(url, "sharer", params);
            }
        }

        // Admin edit link
        if (element.matches(".admin-edit")) {
            event.preventDefault();

            let targetModal = document.querySelector(
                element.dataset.whTargetModal
            );
            let targetModel = element.dataset.whTargetModel;
            let targetForm = document.querySelector(
                element.dataset.whTargetForm
            );
            let targetData = JSON.parse(element.dataset.whTargetFormData);

            if ("category" === targetModel) {
                targetForm.id.value = targetData.id;
                targetForm.name.value = targetData.name;
                targetForm.parent.value = targetData.parent_id;
                targetForm.description.value = targetData.description;
            }

            if ("page" === targetModel) {
                targetForm.id.value = targetData.id;
                targetForm.title.value = targetData.title;
                targetForm.text.value = targetData.text;
            }

            fx.showModal(targetModal, {
                backdrop: "static",
            });
        }

        // Deleting a record (confirmation prompt)
        if (
            element.matches(".admin-content-delete") ||
            element.matches(".user-content-delete")
        ) {
            event.preventDefault();

            let targetModal = element.attributes["href"].value;
            let btnDelete = document.querySelector("#btn-modal-delete");
            let warningDelete = document.querySelector(
                "#content-delete-warning"
            );

            if (!fx.isNil(warningDelete) && "whWarning" in element.dataset) {
                warningDelete.innerHTML = element.dataset.whWarning;
                warningDelete.parentElement.classList.remove("d-none");
            }

            if (!fx.isNil(btnDelete) && "whTarget" in element.dataset) {
                btnDelete.dataset.whDeleteUrl = element.dataset.whTarget;
            }

            if (!fx.isNil(btnDelete) && "whRedirect" in element.dataset) {
                btnDelete.dataset.whRedirectUrl = element.dataset.whRedirect;
            }

            fx.showModal(targetModal, {
                backdrop: "static",
            });
        }

        // Deleting a record
        if (element.matches("#btn-modal-delete")) {
            let url = element.dataset.whDeleteUrl;
            let redirect = element.dataset.whRedirectUrl;
            let params = new FormData();

            params.append("_method", "delete");

            if (!fx.isNilOrEmpty(redirect)) {
                params.append("redirect", true);
            }

            // Post the form to the server
            axios
                .post(url, params)
                .then((response) => {
                    if (!fx.isNilOrEmpty(redirect)) {
                        location.assign(redirect);
                    } else {
                        fx.showToast({
                            message: response.data.message,
                            theme: "success",
                        });
                    }
                })
                .catch((error) => {
                    fx.showToast({
                        message: error.response.data.message,
                        theme: "danger",
                    });
                })
                .then(() => {
                    //
                });
        }

        // Opting out of GA tracking
        if (element.hasAttribute("href") && "#ga-optout" === element.href) {
            event.preventDefault();
            let disableStr = `ga-disable-${analytics_id}`;

            document.cookie = `${disableStr}=true; expires=Thu, 31 Dec 2099 23:59:59 UTC; path=/`;
            window[disableStr] = true;
            alert("Google Analytics tracking disabled");
        }

        // Toggle main sidebar
        if (element.closest(".sidebar-toggle")) {
            element
                .closest(".sidebar-toggle")
                .querySelector("i")
                .classList.toggle("rotate");
        }

        // Firing the content complaint modal
        if (element.matches(".init-complaint")) {
            event.preventDefault();
            document
                .querySelectorAll(".modal-complaint")
                .forEach((e) => e.remove());

            axios
                .get(element.href)
                .then((response) => {
                    document.body.insertAdjacentHTML(
                        "beforeend",
                        response.data
                    );
                })
                .catch((error) => {
                    //
                })
                .then(() => {
                    let complaintModalElement =
                        document.querySelector(".modal-complaint");
                    let complaintModal = new bootstrap.Modal(
                        complaintModalElement
                    );
                    complaintModal.show();
                });
        }

        // Firing the block user modal
        if (element.matches(".init-block-user")) {
            event.preventDefault();
            document
                .querySelectorAll(".modal-complaint")
                .forEach((e) => e.remove());

            axios
                .get(element.href)
                .then((response) => {
                    document.body.insertAdjacentHTML(
                        "beforeend",
                        response.data
                    );
                })
                .catch((error) => {
                    //
                })
                .then(() => {
                    let complaintModalElement =
                        document.querySelector(".modal-complaint");
                    let complaintModal = new bootstrap.Modal(
                        complaintModalElement
                    );
                    complaintModal.show();
                });
        }

        // Blocking a user
        if (element.matches(".block-user-submit")) {
            event.preventDefault();

            let url = element.dataset.href;

            // Post the form to the server
            axios
                .post(url)
                .then((response) => {
                    // Show toast
                    fx.showToast({
                        theme: "success",
                        message: response.data.message,
                    });
                })
                .catch((error) => {
                    // Show toast
                    fx.showToast({
                        theme: "danger",
                        message: error.response.data.message,
                    });
                })
                .then(() => {
                    // Hide complaint modal
                    let complaintModal = bootstrap.Modal.getInstance(
                        document.querySelector(".modal-complaint")
                    );
                    complaintModal.hide();
                    //complaintModal.dispose();
                });
        }

        // Check if alternative categories are selected
        if (element.matches(".submit-writing")) {
            let mainCatSelectBox = document.querySelector("#main-category");
            let altCatsSelectBox = document.querySelector("#categories");

            if (0 === altCatsSelectBox.selectedOptions.length) {
                mainCatSelectBox.scrollIntoView();
                fx.handleFormErrors({
                    categories: [altCatsSelectBox.validationMessage],
                });
            }
        }

        // Open the writing link (hidden read more)
        if (element.matches(".writing-read-more")) {
            window.location.href = element.dataset.link;
        }
    });

    // Listen to the on submit event on the page and act accordingly
    document.addEventListener("submit", (event) => {
        let element = event.target;

        // Post the writing create/update form
        if (element.matches("#writing-form")) {
            event.preventDefault();
            fx.handleForm(element, "submit");

            // Initialize form and helpers
            let params = new FormData(element);
            let url = element.action;

            // Post the form to the server
            axios
                .post(url, params)
                .then((response) => {
                    let method = element.elements["_method"] || false;

                    // Form posted successfully, let's reset it
                    if (!method) {
                        element.reset();

                        let subCategories =
                            document.querySelector("#categories");
                        let tagsInstance = Tags.getInstance(subCategories);

                        subCategories.disabled = true;
                        tagsInstance.resetState();
                    }

                    // Update file helpers
                    element
                        .querySelector("#selected-file")
                        .classList.add("d-none");
                    element
                        .querySelector("#selected-error")
                        .classList.add("d-none");

                    // Remove the agreements section
                    let agreements = document.querySelector("#agreements");

                    if (!fx.isNilOrEmpty(agreements)) {
                        agreements.remove();
                    }

                    // Show toast
                    fx.showToast({
                        theme: "success",
                        message: response.data.message,
                    });
                })
                .catch((error) => {
                    // Oh no, something went wrong
                    let errors = error.response.data.errors;

                    // Handle the error messages
                    fx.handleFormErrors(errors);

                    // Show toast
                    fx.showToast({
                        theme: "danger",
                        message: error.response.data.message,
                    });
                })
                .then(() => {
                    fx.handleForm(element, "response");

                    // Scroll back to the form header
                    document
                        .querySelector("#writing-form-wrapper .block-title")
                        .scrollIntoView({ behavior: "smooth", block: "end" });
                });
        }

        // Post the user profile update form
        if (element.matches("#profile-form")) {
            event.preventDefault();
            fx.handleForm(element, "submit");

            // Initialize form and helpers
            let params = new FormData(element);
            let url = element.action;

            // Post the form to the server
            axios
                .post(url, params)
                .then((response) => {
                    // Remove the agreements section
                    let agreements = document.querySelector("#agreements");

                    if (!fx.isNilOrEmpty(agreements)) {
                        agreements.remove();
                    }

                    // Show toast
                    fx.showToast({
                        message: response.data.message,
                        theme: "success",
                    });
                })
                .catch((error) => {
                    // Oh no, something went wrong
                    let errors = error.response.data.errors;

                    // Handle the error messages
                    fx.handleFormErrors(errors);

                    // Show toast
                    fx.showToast({
                        message: error.response.data.message,
                        theme: "danger",
                    });
                })
                .then(() => {
                    fx.handleForm(element, "response");

                    // Scroll back to the form header
                    document
                        .querySelector("#profile-form-wrapper .title")
                        .scrollIntoView({ behavior: "smooth", block: "end" });
                });
        }

        // Post the comment form
        if (element.matches(".comment-form")) {
            event.preventDefault();

            let params = new FormData(element);
            let url = element.action;
            let commentList = document.querySelector(
                "#embed-comments .comment-list"
            );
            let postCommentSuccess = document.querySelector(
                "#post-comment-success"
            );
            let postCommentError = document.querySelector(
                "#post-comment-error"
            );
            let commentsEmpty = document.querySelector(".comments-empty");

            // Display the wait cursor
            document.body.classList.add("cursor-wait");

            axios
                .post(url, params)
                .then((response) => {
                    element.reset();
                    commentList.insertAdjacentHTML("beforeend", response.data);
                    postCommentSuccess.classList.remove("d-none");
                    postCommentError.classList.add("d-none");

                    if (!fx.isNil(commentsEmpty)) {
                        commentsEmpty.classList.add("d-none");
                    }
                })
                .catch((error) => {
                    postCommentError.textContent =
                        error.response.data.errors.comment[0];
                    postCommentSuccess.classList.add("d-none");
                    postCommentError.classList.remove("d-none");
                })
                .then(() => {
                    // Display the standard cursor
                    document.body.classList.remove("cursor-wait");
                });
        }

        let adminForms = [
            "admin-settings-form",
            "admin-types-form",
            "admin-categories-form",
            "admin-pages-form",
        ];

        // Save data from admin panel
        if (adminForms.includes(element.id)) {
            event.preventDefault();
            fx.handleForm(element, "submit");

            // Initialize form and helpers
            let params = new FormData(element);
            let url = element.action;

            // Post the form to the server
            axios
                .post(url, params)
                .then((response) => {
                    // Reset form
                    if ("create" === response.data.action) {
                        element.reset.click();
                    }

                    fx.showToast({
                        message: response.data.message,
                        theme: "success",
                    });
                })
                .catch((error) => {
                    // Oh no, something went wrong
                    let errors = error.response.data.errors;

                    // Handle the error messages
                    fx.handleFormErrors(errors);

                    fx.showToast({
                        message: error.response.data.message,
                        theme: "danger",
                    });
                })
                .then(() => {
                    fx.handleForm(element, "response");
                });
        }

        // Post the complaint form
        if (element.matches(".form-post-complaint")) {
            event.preventDefault();
            fx.handleForm(element, "submit");

            // Initialize form and helpers
            let params = new FormData(element);
            let url = element.action;

            // Post the form to the server
            axios
                .post(url, params)
                .then((response) => {
                    // Show toast
                    fx.showToast({
                        theme: "success",
                        message: response.data.message,
                    });

                    // Hide complaint modal
                    let complaintModal = bootstrap.Modal.getInstance(
                        document.querySelector(".modal-complaint")
                    );
                    complaintModal.hide();
                    //complaintModal.dispose();
                })
                .catch((error) => {
                    // Show toast
                    fx.showToast({
                        theme: "danger",
                        message: error.response.data.message,
                    });
                })
                .then(() => {
                    fx.handleForm(element, "response");
                });
        }
    });

    // Listen to the on change event on the page and act accordingly
    document.addEventListener("change", (event) => {
        let element = event.target;

        // Manage push notifications
        if (element.matches("#enable-push-notifications")) {
            if (element.checked) {
                push.subscribe();
                element.dispatchEvent(new Event("focusout", { bubbles: true }));
            } else {
                push.unsubscribe();
                element.dispatchEvent(new Event("focusout", { bubbles: true }));
            }
        }

        // Manage email notifications
        if (element.matches("#enable-email-notifications")) {
            axios
                .post(`notifications/email/${element.checked}`)
                .then((response) => {
                    //
                })
                .catch((error) => {
                    //
                });
        }

        // Trigger the cover file validation
        if (element.matches("#cover")) {
            const file = element.files[0];
            const fileSizeKb = parseInt(file.size / 1024);
            const maxFileSizeKb = element.dataset.whMaxSize;
            let info = document.querySelector(element.dataset.whTarget);
            let error = element.parentElement.querySelector("#selected-error");

            if (
                !fx.isNilOrEmpty(file) &&
                fileSizeKb <= maxFileSizeKb &&
                fx.isImage(file)
            ) {
                info.textContent = `${file.name} [ ${fileSizeKb} kb]`;
                info.classList.remove("d-none");
                error.classList.add("d-none");
            } else {
                element.value = "";
                info.textContent = "";
                info.classList.add("d-none");
                error.classList.remove("d-none");
            }
        }

        // Trigger the avatar file validation
        if (element.matches("#avatar")) {
            const file = element.files[0];
            const fileSizeKb = parseInt(file.size / 1024);
            const maxFileSizeKb = element.dataset.whMaxSize;
            let error = document.querySelector("#avatar-error");

            if (
                !fx.isNilOrEmpty(file) &&
                fileSizeKb <= maxFileSizeKb &&
                fx.isImage(file)
            ) {
                fx.readImage(file, fx.previewAvatar);
                error.classList.add("d-none");
            } else {
                element.value = "";
                fx.previewAvatar("");
                error.classList.remove("d-none");
            }
        }

        // Trigger the avatar delete checkbox validation
        if (element.matches("#avatar-remove")) {
            if (element.checked) {
                let avatarInput = document.querySelector("#avatar");

                fx.previewAvatar("");
                avatarInput.value = "";
            }
        }

        // Update alternative categories
        if (element.matches("#main-category")) {
            let subCategories = document.querySelector("#categories");
            let tagsInstance = Tags.getInstance(subCategories);

            fx.clearSelections(subCategories);

            if (fx.isNilOrEmpty(element.value)) {
                subCategories.disabled = true;
            } else {
                fx.subCategoriesUpdate(subCategories, element);
                subCategories.disabled = false;
            }

            tagsInstance.removeAll();
            tagsInstance.resetSuggestions();
            tagsInstance.resetState();
        }
    });

    // Listen to the window scroll event and act accordingly
    window.addEventListener("scroll", (event) => {
        let jumpToMainNav = document.querySelector(".jump-to-top");
        let newScrollPos = window.scrollY;

        // Clear visibility classes
        jumpToMainNav.classList.remove("fade-in");
        jumpToMainNav.classList.remove("fade-out");

        if (!fx.isNilOrEmpty(jumpToMainNav)) {
            if (newScrollPos > oldScrollPos || 0 == newScrollPos) {
                jumpToMainNav.classList.add("fade-out");
            } else {
                jumpToMainNav.classList.add("fade-in");
            }

            // Save current scroll position.
            oldScrollPos = newScrollPos;
        }
    });

    // Listen to the input event and act accordingly
    document.addEventListener("input", (event) => {
        let element = event.target;

        if (element.matches(".autogrow")) {
            autoGrow(element);
        }
    });

    let tribute = new Tribute({
        trigger: "@",
        menuShowMinLength: 1,
        lookup: "username",
        fillAttr: "username",
        searchOpts: {
            pre: "<span>",
            post: "</span>",
            skip: true,
        },
        noMatchTemplate: () => '<span class="d-none"></span>',
        loadingItemTemplate:
            () => `<div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>`,
        menuItemTemplate: (item) =>
            `${
                item.original.name ?? item.original.username
            } <span class="text-muted">@${item.original.username}</span>`,
        values: fx.getUserList,
    });

    tribute.attach(document.querySelectorAll(".commentbox"));
    window.tribute = tribute; // Hack to make Tribute available when loading comments. Needs rework
});
