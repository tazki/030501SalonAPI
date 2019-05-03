// var baseUrl = 'https://nailartists.app/api/index.php/';
var baseUrl = 'http://localhost:10/3.1.10/nailartists/index.php/';
// var baseUrl = 'http://localhost/3.1.6/haku_salon/index.php/';
var imgbaseUrl = 'https://nailartists.app/api/';

$(document).ready(function () {
    var currentPage = $('.contentHolder').data('page');
    var postID = $('.contentHolder').data('post');
    if (currentPage == 'home') {
        $.ajax({
            url: baseUrl + 'api/post?user_id=' + d0de94 + '&list_type=trending',
            type: "GET"
        }).fail(function () {}).done(function (data) {
            console.log(data);
            $.each(data.rows, function (key, val) {
                var avatarHolder = '<img class="rounded-circle nailartist-profile-img align-self-center lazy" data-original="' + imgbaseUrl + 'img/user-bg.png" />';
                if (val.user_avatar != null) {
                    avatarHolder = '<img class="rounded-circle nailartist-profile-img align-self-center lazy" data-original="' + val.user_avatar + '" />';
                }

                var item = '<a href="' + baseUrl + 'detail/' + val.haku_id + '" class="post-detail-link">' +
                    '<div class="card nailartist-card nailartist-card-slider lazy" data-original="' + val.haku_image + '" style="background-image: url(' + val.haku_image + ');">' +
                    '<div class="fader"></div>' +
                    '<div class="card-body">' +
                    '<div class="media nailartist-card-slider-meta">' +
                    avatarHolder +
                    '<div class="media-body align-self-center">' +
                    '<h6 class="nailartist-username mt-0 mb-0 ml-2">' + memberDisplayName(val.user_username, val.user_first_name, val.user_last_name) + '</h6>' +
                    // '<span class="nailartist-date">' + val.readable_date + '</span>' +
                    '</div>' +
                    '</div>' +
                    '</div>' +
                    '</div>' +
                    '</a>';
                $('.nailartist-trending-wrapper').append(item);
            });

            lazyLoadImg();
            $('.nailartist-trending-wrapper').owlCarousel({
                loop: true,
                margin: 10,
                nav: true,
                responsive: {
                    0: {
                        items: 1
                    },
                    600: {
                        items: 3
                    },
                    1000: {
                        items: 6
                    }
                },
                navText: ['<span class="fa-stack"></i><i class="fa fa-arrow-left fa-stack-1x fa-inverse"></i></span>', '<span class="fa-stack"></i><i class="fa fa-arrow-right fa-stack-1x fa-inverse"></i></span>'],
            });
        });

        $.ajax({
            url: baseUrl + 'api/post?user_id=' + d0de94 + '&list_type=latest', // + '&next_page=' + scrollInfiniteCounter,
            type: "GET"
        }).fail(function () {}).done(function (data) {
            $.each(data.rows, function (key, val) {
                var avatarHolder = '<a href="' + baseUrl + 'otherprofile/?otherd0de94=' + val.user_id + '" class="post-detail-link">' + '<img class="rounded-circle nailartist-profile-img align-self-center lazy" data-original="' + imgbaseUrl + 'img/user-bg.png" /></a>';
                if (val.user_avatar != null) {
                    avatarHolder = '<a href="' + baseUrl + 'otherprofile/?otherd0de94=' + val.user_id + '" class="post-detail-link">' + '<img class="rounded-circle nailartist-profile-img align-self-center lazy" data-original="' + val.user_avatar + '" /></a>';
                }

                var likeBtnIcon = 'favorite_border';
                var colorheart = 'heart-not-liked';
                if (val.is_liked == 1) {
                    likeBtnIcon = 'favorite';
                    colorheart = 'heart-liked';
                }

                var commentIcon = '';
                var commentCount = '';
                if (val.haku_allow_comment == 0) {
                    commentIcon = '<a href="' + baseUrl + 'detail/' + val.haku_id + '" class="link"><i class="fa fa-comment" aria-hidden="true"></i></a>';
                    commentCount = '<a href="' + baseUrl + 'detail/' + val.haku_id + '" class="link"></a><span>' + val.haku_comment + '</span>';
                }

                var tagPost = " ";
                if (val.haku_tag_user != null) {
                    var hakuTagUser = '';
                    var hakuTagUserComma = '';
                    $(val.haku_tag_user).each(function (key, val) {

                        if (d0de94 == val.user_id) {
                            isTagged = true;
                        }

                        if (hakuTagUser != '') {
                            hakuTagUserComma = ', ';
                        }

                        hakuTagUser += '<a href="/otherprofile/?otherd0de94=' + val.user_id + '" class="link">' +
                            '<span class="badge badge-secondary">' +
                            val.user_username +
                            '</span>' +
                            '</a>';
                    });
                    tagPost = ' <span style="font-style: italic;">with </span> ' + hakuTagUser;
                }
                var dnone = "";
                if (val.haku_tag_user == null && val.haku_description == "") {
                    dnone = "d-none";
                }
                var item = '<div class="card card-nailartist">' +
                    '<a href="' + baseUrl + 'detail/' + val.haku_id + '" class="post-detail-link">' +
                    '<img class="card-img-top lazy" data-original="' + val.haku_image + '" />' +
                    '</a>' +
                    '<div class="card-body">' +
                    '<div class="media mb-2">' +
                    avatarHolder +
                    '<div class="media-body align-self-center ml-2">' +
                    '<a href="' + baseUrl + 'otherprofile/?otherd0de94=' + val.user_id + '">' + '<h6 class="nailartist-username mt-0 mb-0">' + memberDisplayName(val.user_username, val.user_first_name, val.user_last_name) + '</h6></a>' +
                    '<span class="nailartist-location">' + val.haku_location + '</span>' +
                    '</div>' +
                    '</div>' +
                    '<p class="card-text nailartist-caption mb-0 ' + dnone + '">' + val.haku_description + '</p>' +
                    '<p class="card-text nailartist-tag  mb-2 ' + dnone + '">' + tagPost + '</p>' +
                    '<p class="nailartist-date mt-0 mb-0">' + val.readable_date + '</p>' +
                    '</div>' +
                    '<div class="card-footer">' +
                    '<small class="text-center">' +
                    '<div class="row">' +
                    '<div class="col col-lg-4">' +
                    '<a href="javascript:void(0);" class="link post-like-btn-' + val.haku_id + '" onClick="likePost(this, ' + val.haku_id + ', ' + d0de94 + ')">' +
                    '<span class="post-count-' + val.haku_id + '">' +
                    '<i class="fa fa-heart ' + colorheart + '" aria-hidden="true"></i> ' +
                    '</span>' +
                    '</a>' +
                    '<span>' + val.haku_like + '</span>' +
                    '</div>' +
                    '<div class="col col-lg-4">' +
                    '<span>' +
                    commentIcon + ' ' + commentCount +
                    '</span>' +
                    '</div>' +
                    '<div class="col col-lg-4">' +
                    '<a class="share-nailart" data-share="home" data-toggle="modal" data-target="#nailartist-share-modal" href="javascript:void(0);">' +
                    '<i class="fa fa-share" aria-hidden="true"></i>' +
                    '</a>' +
                    '</div>' +
                    '</div>' +
                    '</small>' +
                    '</div>' +
                    '</div>';
                //'<a href="#" class="link share-link" data-d0de94="' + val.user_id + '" data-postid="' + val.haku_id + '" data-description="' + val.haku_description + '" data-url="' + val.haku_image + '"><i class="material-icons" style="color:#747474; margin-right:10px;">share</i></a>' +
                $('.home-latest-holder').append(item);
            });
            lazyLoadImg();
        });
    } else if (currentPage == 'post-detail') {
        $.ajax({
            url: baseUrl + 'api/post?user_id=' + d0de94 + '&post_id=' + postID,
            type: "GET"
        }).fail(function () {}).done(function (data) {
            console.log(data);
            // updatePostView(postID);
            listComment(postID, d0de94);

            $.each(data.rows, function (key, val) {
                var avatarHolder = '<a href="/otherprofile/?otherd0de94=' + val.user_id + '" class="link">' + '<img class="ml-2 mr-2 rounded-circle nailartist-profile-img  align-self-center lazy" data-original="' + imgbaseUrl + 'img/user-bg.png" /></a>';
                if (val.user_avatar != null) {
                    avatarHolder = '<a href="/otherprofile/?otherd0de94=' + val.user_id + '" class="link">' + '<img class="ml-2 mr-2 rounded-circle nailartist-profile-img  align-self-center lazy" data-original="' + val.user_avatar + '"/></a>';
                }

                var likeBtnIcon = 'favorite_border';
                var colorheart = 'heart-not-liked';
                if (val.is_liked == 1) {
                    likeBtnIcon = 'favorite';
                    colorheart = 'heart-liked';
                }

                var commentIcon = '';
                var commentCount = '';
                if (val.haku_allow_comment == 0) {
                    commentIcon = '<a href="/comments/?id=' + val.haku_id + '" class="link"><i class="fa fa-comment" aria-hidden="true"></i></a>';
                    commentCount = '<a href="/comments/?id=' + val.haku_id + '" class="link"></a><span>' + val.haku_comment + '</span>';
                }

                var tagPost = " ";
                if (val.haku_tag_user != null) {
                    $(val.haku_tag_user).each(function (key, val) {
                        tagPost += '<a href="/otherprofile/?otherd0de94=' + val.user_id + '" class="link">' +
                            '<span class="badge badge-secondary">' +
                            val.user_username +
                            '</span>' +
                            '</a>';
                    });
                }
                if (val.haku_description == "") {
                    $('.nailartist-description').addClass('d-none');
                } else {
                    $('.nailartist-description').html(val.haku_description);
                }

                var dnone = "";
                if (val.haku_tag_user == null) {
                    $('.nailartist-tag').addClass('d-none');
                } else {
                    $('.nailartist-tag').html(tagPost);
                }

                var commentLabel = " Comments";
                if (val.haku_comment <= 1) {
                    commentLabel = " Comment";
                }

                $('#comment-counter-label').html(val.haku_comment + commentLabel);
                $('.nailartist-avatar').html(avatarHolder);
                $('.nailartist-username').html('<a href="/otherprofile/?otherd0de94=' + val.user_id + '" class="link text-decor-none">' + val.user_username + '</a>');
                $('.nailartist-location').html(val.haku_location);
                $('.nailartist-date').html(val.readable_date);
                $('.nailartist-img').attr('data-original', val.haku_image);

                var postdetailFooter = '<div class="card-footer">' +
                    '<small class="text-center">' +
                    '<div class="row">' +
                    '<div class="col col-lg-4">' +
                    '<a href="javascript:void(0);" class="link post-like-btn-' + val.haku_id + '" onClick="likePost(this, ' + val.haku_id + ', ' + d0de94 + ')">' +
                    '<span class="post-count-' + val.haku_id + '">' +
                    '<i class="fa fa-heart ' + colorheart + '" aria-hidden="true"></i> ' +
                    '</span>' +
                    '</a>' +
                    '<span>' + val.haku_like + '</span>' +
                    '</div>' +
                    '<div class="col col-lg-4">' +
                    '<span>' +
                    commentIcon + ' ' + commentCount +
                    '</span>' +
                    '</div>' +
                    '<div class="col col-lg-4">' +
                    '<a class="share-nailart" data-share="post" data-toggle="modal" data-target="#nailartist-share-modal" href="javascript:void(0);">' +
                    '<i class="fa fa-share" aria-hidden="true"></i>' +
                    '</a>' +
                    '</div>' +
                    '</div>' +
                    '</small>' +
                    '</div>';

                $(postdetailFooter).insertAfter('.card-body');
            });
            lazyLoadImg();
        });
    } else if (currentPage == 'follow') {
        listOtherMember(d0de94);
    }

    var page = 2;
    $(window).scroll(function () {
        if ($(window).scrollTop() == $(document).height() - $(window).height()) {
            $.ajax({
                url: baseUrl + 'api/post?user_id=' + d0de94 + '&list_type=latest&next_page=' + page,
                type: "GET"
            }).fail(function () {}).done(function (data) {
                if (page == data.total_page || data.total_page == undefined) {
                    return false;
                }
                $.each(data.rows, function (key, val) {
                    var avatarHolder = '<a href="' + baseUrl + 'otherprofile/?otherd0de94=' + val.user_id + '" class="post-detail-link">' + '<img class="ml-2 mr-2 rounded-circle nailartist-profile-img align-self-center lazy" data-original="' + imgbaseUrl + 'img/user-bg.png" /></a>';
                    if (val.user_avatar != null) {
                        avatarHolder = '<a href="' + baseUrl + 'otherprofile/?otherd0de94=' + val.user_id + '" class="post-detail-link">' + '<img class="ml-2 mr-2 rounded-circle nailartist-profile-img align-self-center lazy" data-original="' + val.user_avatar + '" /></a>';
                    }

                    var likeBtnIcon = 'favorite_border';
                    var colorheart = 'heart-not-liked';
                    if (val.is_liked == 1) {
                        likeBtnIcon = 'favorite';
                        colorheart = 'heart-liked';
                    }

                    var commentIcon = '';
                    var commentCount = '';
                    if (val.haku_allow_comment == 0) {
                        commentIcon = '<a href="' + baseUrl + 'detail/' + val.haku_id + '" class="link"><i class="fa fa-comment" aria-hidden="true"></i></a>';
                        commentCount = '<a href="' + baseUrl + 'detail/' + val.haku_id + '" class="link"></a><span>' + val.haku_comment + '</span>';
                    }

                    var tagPost = " ";
                    if (val.haku_tag_user != null) {
                        var hakuTagUser = '';
                        var hakuTagUserComma = '';
                        $(val.haku_tag_user).each(function (key, val) {

                            if (d0de94 == val.user_id) {
                                isTagged = true;
                            }

                            if (hakuTagUser != '') {
                                hakuTagUserComma = ', ';
                            }

                            hakuTagUser += '<a href="/otherprofile/?otherd0de94=' + val.user_id + '" class="link">' +
                                '<span class="badge badge-secondary">' +
                                val.user_username +
                                '</span>' +
                                '</a>';
                        });
                        tagPost = ' <span style="font-style: italic;">with </span> ' + hakuTagUser;
                    }
                    var dnone = "";
                    if (val.haku_tag_user == null && val.haku_description == "") {
                        dnone = "d-none";
                    }
                    var item = '<div class="card nailartist-card">' +
                        '<a href="' + baseUrl + 'detail/' + val.haku_id + '" class="post-detail-link">' +
                        '<img class="card-img-top lazy" data-original="' + val.haku_image + '" />' +
                        '</a>' +
                        '<div class="card-body">' +
                        '<div class="media mb-2">' +
                        avatarHolder +
                        '<div class="media-body align-self-center ml-2">' +
                        '<a href="' + baseUrl + 'otherprofile/?otherd0de94=' + val.user_id + '">' + '<h6 class="nailartist-username mt-0 mb-0">' + memberDisplayName(val.user_username, val.user_first_name, val.user_last_name) + '</h6></a>' +
                        '<span class="nailartist-location">' + val.haku_location + '</span>' +
                        '</div>' +
                        '</div>' +
                        '<p class="card-text nailartist-caption mb-0 ' + dnone + '">' + val.haku_description + '</p>' +
                        '<p class="card-text nailartist-tag mb-2 ' + dnone + '">' + tagPost + '</p>' +
                        '<p class="nailartist-date mt-0 mb-0">' + val.readable_date + '</p>' +
                        '</div>' +
                        '<div class="card-footer">' +
                        '<small class="text-center">' +
                        '<div class="row">' +
                        '<div class="col col-lg-4">' +
                        '<a href="javascript:void(0);" class="link post-like-btn-' + val.haku_id + '" onClick="likePost(this, ' + val.haku_id + ', ' + d0de94 + ')">' +
                        '<span class="post-count-' + val.haku_id + '">' +
                        '<i class="fa fa-heart ' + colorheart + '" aria-hidden="true"></i> ' +
                        '</span>' +
                        '</a>' +
                        '<span>' + val.haku_like + '</span>' +
                        '</div>' +
                        '<div class="col col-lg-4">' +
                        '<span>' +
                        commentIcon + ' ' + commentCount +
                        '</span>' +
                        '</div>' +
                        '<div class="col col-lg-4">' +
                        '<a class="share-nailart" data-share="home" data-toggle="modal" data-target="#nailartist-share-modal" href="javascript:void(0);">' +
                        '<i class="fa fa-share" aria-hidden="true"></i>' +
                        '</a>' +
                        '</div>' +
                        '</div>' +
                        '</small>' +
                        '</div>' +
                        '</div>';
                    //'<a href="#" class="link share-link" data-d0de94="' + val.user_id + '" data-postid="' + val.haku_id + '" data-description="' + val.haku_description + '" data-url="' + val.haku_image + '"><i class="material-icons" style="color:#747474; margin-right:10px;">share</i></a>' +
                    $('.home-latest-holder').append(item);
                });
                lazyLoadImg();
            });
            page++;
        }
    });
});


$(document).on('click', '.share-nailart', function (e) {
    var preview = $(this).parents('.card').html();
    $("#preview-holder").append(preview);


    if ($(this).data("share") == "home") {
        var URL = $(this).parents('.card').find('.post-detail-link').attr("href");
        var text = $(this).parents('.card').find('.card-text').text();
    } else {
        var URL = window.location.href;
        var text = $(this).parents('.card').find('.post-description').text();
    }

    $(".btn-facebook").attr("href", "https://www.facebook.com/sharer.php?u=" + URL + "")
    $(".btn-pinterest").attr("href", "http://pinterest.com/pin/create/link/?url=" + URL + "")
    $(".btn-twitter").attr("href", "https://twitter.com/intent/tweet?url=" + URL + "&text=" + text + "")
    $(".btn-line").attr("href", "https://lineit.line.me/share/ui?url=" + URL + "&text=" + text + "")
});


$('#nailartist-share-modal').on('hidden.bs.modal', function () {
    $('#nailartist-share-modal #nailartist-share-preview').html("");
})


$(window).scroll(function () {
    if ($(this).scrollTop() > 50) {
        $('.scrolltop:hidden').stop(true, true).fadeIn();
    } else {
        $('.scrolltop').stop(true, true).fadeOut();
    }
});
$(function () {
    $(".scroll").click(function () {
        $("html, body").animate({
            scrollTop: 0
        }, "slow");
        return false
    })
})


function showCommentInput(obj, postID, d0de94, replyID) {
    var avatarHolder = '<img class="lazy" data-original="' + imgbaseUrl + 'img/user-bg.png" />';

    if (localStorage.user_avatar != null) {
        avatarHolder = '<a href="/otherprofile/?otherd0de94=' + d0de94 + '"><img class="mx-auto rounded-circle img-fluid lazy" data-original="' + localStorage.user_avatar + '"/></a>';
    }

    var userComment = '<div class="row mt-2 mb-4">' +
        '<div class="comment-avatar col-md-1 col-sm-2 text-center pr-1">' +
        avatarHolder +
        '</div>' +
        '<div class="col-md-11 col-sm-10">' +
        '<div class="form-group">' +
        '<textarea placeholder="Say something..." class="comment-data form-control" id="exampleFormControlTextarea1" rows="3"></textarea>' +
        '</div>' +
        '<a href="#" onClick="addComment(this, ' + postID + ', ' + d0de94 + ', ' + replyID + ')" class="btn btn-sm btn-primary">Comment</a>' +
        '</div>' +
        '</div>';
    $(obj).parent().parent().after(userComment);
    lazyLoadImg();
}

function addComment(obj, postID, d0de94, replyID) {
    commentData = $('.comment-data').val();
    if (commentData != '') {
        $(obj).text('Loading...');
        var URL = baseUrl + 'api/comment/add?user_id=' + d0de94 + '&haku_id=' + postID + '&comment=' + commentData;
        console.log('replyID:' + replyID);
        if (replyID != undefined) {
            URL += '&reply_id=' + replyID;
        }
        console.log(URL);
        $.ajax({
            url: URL,
            type: "GET"
        }).fail(function (data) {
            alert("F");
            console.log(data);
        }).done(function (data) {
            alert("D");
            $('.commentListHolder').html("");
            listComment(postID, d0de94)

        });
    } else {}
}

function listComment(postID, d0de94) {

    var avatarHolder = '<img class="lazy" data-original="' + imgbaseUrl + 'img/user-bg.png" />';

    if (localStorage.user_avatar != null) {
        avatarHolder = '<a href="/otherprofile/?otherd0de94=' + d0de94 + '"><img class="mx-auto rounded-circle img-fluid nailartist-profile-img lazy" data-original="' + localStorage.user_avatar + '" /></a>';
    }

    var userComment = '<div class="row mt-2 mb-4">' +
        '<div class="comment-avatar col-md-1 col-sm-2 text-center pr-1">' +
        avatarHolder +
        '</div>' +
        '<div class="col-md-11 col-sm-10">' +
        '<div class="form-group">' +
        '<textarea placeholder="Say something..." class="comment-data form-control" rows="3"></textarea>' +
        '</div>' +
        '<a href="#" onClick="addComment(this, ' + postID + ', ' + d0de94 + ')" class="btn btn-sm btn-primary">Comment</a>' +
        '</div>' +
        '</div>';

    $('.commentListHolder').append(userComment);
    // lazyLoadImg();
    $.ajax({
        url: baseUrl + 'api/comment?haku_id=' + postID,
        type: "GET"
    }).fail(function (data) {
        console.log(data);
        return false;
    }).done(function (data) {
        console.log(data);
        $.each(data, function (key, val) {
            var avatarHolder = '<img class="rounded-circle nailartist-profile-img lazy" data-original="' + imgbaseUrl + 'img/user-bg.png" />';
            if (val.user_avatar != null) {
                avatarHolder = '<img class="rounded-circle nailartist-profile-img lazy" data-original="' + val.user_avatar + '" />';
            }

            var item = '<div class="comment-avatar col-md-1 col-sm-2 text-center pr-1">' +
                '<a href="">' +
                avatarHolder +
                '</a>' +
                '</div>' +
                '<div class="comment-content col-md-11 col-sm-10">' +
                '<h6 class="small nailartist-comment-meta m-0">' +
                '<a href="#">' +
                memberDisplayName(val.user_username, val.user_first_name, val.user_last_name) +
                '</a>' +
                '<p class="float-right">' + val.readable_date + '</p>' +
                '</h6>' +
                '<div class="nailartist-comment-body">' +
                '<p>' +
                val.comment +
                '<br>' +
                '<a href="#" class="text-right small" onClick="showCommentInput(this, ' + val.haku_id + ', ' + d0de94 + ', ' + val.comment_id + ')">' +
                '<i class="fa fa-reply"></i> Reply' +
                '</a>' +
                '</p>' +
                '</div>' +
                '</div>';

            subitem = '';
            if (val.comment_reply != undefined) {
                $.each(val.comment_reply, function (skey, sval) {
                    var avatarHolder = '<img class="rounded-circle nailartist-profile-img lazy" data-original="' + imgbaseUrl + 'img/user-bg.png" />';
                    if (sval.user_avatar != null) {
                        avatarHolder = '<img class="rounded-circle nailartist-profile-img lazy" data-original="' + sval.user_avatar + '" />';
                    }

                    subitem += '<div class="comment-reply col-md-11 offset-md-1 col-sm-10 offset-sm-2">' +
                        '<div class="row">' +
                        '<div class="comment-avatar col-md-1 col-sm-2 text-center pr-1">' +
                        '<a href="">' + avatarHolder + '</a>' +
                        '</div>' +
                        '<div class="comment-content col-md-11 col-sm-10 col-12">' +
                        '<h6 class="small comment-meta"><a href="#">' + memberDisplayName(sval.user_username, sval.user_first_name, sval.user_last_name) + '</a></h6>' +
                        '<p class="float-right">' + val.readable_date + '</p>' +
                        '<div class="nailartist-comment-body">' +
                        '<p>' + sval.comment +
                        '<br>' +
                        '<a href="#" class="text-right small" onClick="showCommentInput(this, ' + sval.haku_id + ', ' + d0de94 + ', ' + val.comment_id + ')">' +
                        '<i class="fa fa-reply"></i> Reply' +
                        '</a>' +
                        '</p>' +
                        '</div>' +
                        '</div>' +
                        '</div>' +
                        '</div>';
                });
            }

            var mainItem = '<div class="comment mb-2 row">' +
                item +
                subitem +
                '</div>';
            $('.commentListHolder').append(mainItem);
            lazyLoadImg();
        });
    });
}

function likePost(obj, postId, followedByd0de94) {
    console.log(obj);
    origPostCount = parseInt($('.post-count-' + postId).text());
    var origLabel = $(obj).find('span').html();
    console.log(origLabel);
    $.ajax({
        url: baseUrl + 'api/post/likepost/' + postId + '/' + followedByd0de94,
        type: "GET"
    }).fail(function () {}).done(function (data) {
        if (origLabel == '<i class="fa fa-heart heart-liked" aria-hidden="true"></i> ') {
            console.log("if");
            $('.post-like-btn-' + postId).find('i.fa').removeClass('heart-liked').addClass('heart-not-liked');
            $('.post-count-' + postId).parent().next().text(data.view_count);
        } else {
            console.log("else");
            $('.post-like-btn-' + postId).find('i.fa').removeClass('heart-not-liked').addClass('heart-liked');
            $('.post-count-' + postId).parent().next().text(data.view_count);
        }
    });
}

function listOtherMember(d0de94, query) {
    if (query != undefined) {
        query = '&q=' + query;
    } else {
        query = '';
    }

    $('.tofollow-container').html('');
    console.log(baseUrl + 'api/member/memberlist?is_followed_user=false&user_id=' + d0de94 + query);
    $.ajax({
        url: baseUrl + 'api/member/memberlist?is_followed_user=false&user_id=' + d0de94 + query,
        type: "GET"
    }).fail(function () {}).done(function (data) {
        if (data.status != false && ($.type(data) == 'object' || 'array')) {
            $.each(data, function (key, val) {
                var avatarHolder = '<img class="nailartist-profile-img rounded-circle lazy" data-src="' + imgbaseUrl + 'img/user-bg.png" />';
                if (val.user_avatar != null) {
                    avatarHolder = '<img class="nailartist-profile-img rounded-circle lazy" data-src="' + val.user_avatar + '" />';
                }

                var followBtnLabel = 'Follow';
                if (val.is_followed == 1) {
                    followBtnLabel = 'Unfollow';
                }

                var followLabel = "followers";

                if (val.user_follower_count == 0 || val.user_follower_count == 1) {
                    followLabel = "follower";
                }

                var item = '<div class="col-md-3">' +
                    '<div class="card nailartist-card nailartist-card-follow">' +
                    '<div class="card-header"></div>' +
                    '<div class="card-block text-center">' +
                    avatarHolder +
                    '<p class="nailartist-username mb-0"><strong>' +
                    memberDisplayName(val.user_username, val.user_first_name, val.user_last_name) +
                    '</strong></p>' +
                    // '<p class="author-location"><strong>Manila, Philippines</strong></p>' +
                    '</div>' +
                    '<div class="container">' +
                    '<div class="row align-items-center text-center">' +
                    '<div class="col-sm">' +
                    '0 posts' +
                    '</div>' +
                    '<div class="col-sm follow-follower-count">' +
                    '<span class="user-count-' + val.user_id + '">' + val.user_follower_count + '</span> ' + followLabel +
                    '</div>' +
                    '</div>' +
                    '<button type="button" class="btn btn-primary btn-block mb-3 mt-3 search-follow" onClick="followMember(this, ' + val.user_id + ', ' + d0de94 + ')">' +
                    followBtnLabel +
                    '</button>' +
                    '</div>' +
                    '</div>' +
                    '</div>';

                $('.tofollow-container').append(item);
                lazyLoadImg();
            });
        } else {
            $('.tofollow-container').html('<div class="row tofollow-holder"><center class="col-100">No Results found.</center></div>');
        }
    });
}

function followMember(obj, followingId, followedByd0de94) {
    origUserCount = parseInt($('.user-count-' + followingId).text());
    origLabel = $(obj).text();
    $(obj).text('Loading...');
    $.ajax({
        url: baseUrl + 'api/member/followmember/' + followingId + '/' + followedByd0de94,
        type: "GET"
    }).fail(function () {}).done(function (data) {
        if (origLabel == 'Follow') {
            $(obj).text('Unfollow');
            $('.user-count-' + followingId).text(data.view_count);
        } else {
            $(obj).text('Follow');
            $('.user-count-' + followingId).text(data.view_count);
        }
    });
}

function memberDisplayName(userName, firstName, lastName) {
    var memberDisplayName = userName;
    if (firstName != null && lastName != null) {
        //memberDisplayName = firstName + ' ' + lastName;
    }

    return memberDisplayName;
}

function lazyLoadImg() {
    $("img.lazy").lazyload({
        effect: "fadeIn"
    });

    $("div.lazy").lazyload({
        effect: "fadeIn"
    });
}
