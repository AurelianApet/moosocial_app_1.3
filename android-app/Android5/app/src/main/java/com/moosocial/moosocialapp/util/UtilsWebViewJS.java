package com.moosocial.moosocialapp.util;

import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.graphics.Bitmap;
import android.net.Uri;
import android.support.v4.util.LogWriter;
import android.util.Log;
import android.webkit.JavascriptInterface;
import android.widget.ImageView;
import android.widget.TextView;

import com.android.volley.Response;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.ImageRequest;
import com.android.volley.toolbox.NetworkImageView;
import com.moosocial.moosocialapp.MooApplication;
import com.moosocial.moosocialapp.R;
import com.moosocial.moosocialapp.presentation.view.activities.MainActivity;

import org.w3c.dom.Text;

/**
 * Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */
public class UtilsWebViewJS {
    Activity aActivity;

    /** Instantiate the interface and set the context */
    public UtilsWebViewJS(Activity c) {
        aActivity = c;
    }

    @JavascriptInterface   // must be added for API 17 or higher
    public void setNameMenu(String name) {
        TextView tName =  (TextView)aActivity.findViewById(R.id.navigation_drawer_account_information_display_name);
        tName.setText(name);
    }

    @JavascriptInterface   // must be added for API 17 or higher
    public void setAvatar(String avatar) {
        Log.wtf("aaaa",avatar);
        final ImageView mImageView =  (ImageView)aActivity.findViewById(R.id.navigation_drawer_user_account_picture_profile);
        MooGlobals.getInstance().getMooImageLoader().DisplayImage(avatar, mImageView);
    }

    @JavascriptInterface   // must be added for API 17 or higher
    public void setCover(final String cover) {
        aActivity.runOnUiThread(new Runnable() {
            @Override
            public void run() {
                NetworkImageView nCover = (NetworkImageView) aActivity.findViewById(R.id.navigation_drawer_user_account_picture_cover);
                nCover.setImageUrl(cover, MooGlobals.getInstance().getmImageLoader());
            }
        });
    }

    @JavascriptInterface   // must be added for API 17 or higher
    public void refeshToken() {
        aActivity.runOnUiThread(new Runnable() {
            @Override
            public void run() {
                ((MainActivity)aActivity).getPresenter().onRefeshToken();
            }
        });
    }
    @JavascriptInterface   // must be added for API 17 or higher
    public void openUrl(String url) {
        Intent browserIntent = new Intent(Intent.ACTION_VIEW, Uri.parse(url));
        aActivity.startActivity(browserIntent);
    }
}
