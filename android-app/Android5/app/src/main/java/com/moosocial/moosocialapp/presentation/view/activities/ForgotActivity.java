package com.moosocial.moosocialapp.presentation.view.activities;

import android.os.Bundle;
import android.support.v7.app.ActionBar;
import android.support.v7.widget.Toolbar;
import android.view.MenuItem;
import android.view.View;
import android.widget.ProgressBar;

import com.moosocial.moosocialapp.R;
import com.moosocial.moosocialapp.presentation.presenter.ForgotPresenter;

/**
 * Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */
public class ForgotActivity extends MooActivity {
    public ActionBar ab;
    public ForgotPresenter sForgotPresenter;
    public ProgressBar pProgressBar;
    public View vContent;
    public View vSuccessful;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_forgot);
        vContent = (View) findViewById(R.id.forgot_content);
        pProgressBar = (ProgressBar) findViewById(R.id.forgot_progress);
        vSuccessful = (View) findViewById(R.id.forgot_successful);

        sForgotPresenter = new ForgotPresenter(this);

        //init toolbar
        Toolbar toolbar = (Toolbar) findViewById(R.id.toolbar);
        setSupportActionBar(toolbar);

        ab = getSupportActionBar();
        ab.setHomeAsUpIndicator(R.drawable.ic_bar_arrow_back);
        ab.setDisplayHomeAsUpEnabled(true);
        ab.setDisplayShowTitleEnabled(true);
        ab.setTitle(getResources().getString(R.string.text_forgot));
    }

    public void showLoading()
    {
        pProgressBar.setVisibility(View.VISIBLE);
        vContent.setVisibility(View.GONE);
    }

    public void hideLoading()
    {
        pProgressBar.setVisibility(View.GONE);
        vContent.setVisibility(View.VISIBLE);
    }

    public void showSuccessful()
    {
        pProgressBar.setVisibility(View.GONE);
        vContent.setVisibility(View.GONE);
        vSuccessful.setVisibility(View.VISIBLE);
    }

    public void forgotAction(View e)
    {
        sForgotPresenter.onForgot();
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        int id = item.getItemId();
        switch (id) {
            case android.R.id.home:
                finish();
                return true;
        }
        return super.onOptionsItemSelected(item);
    }
}