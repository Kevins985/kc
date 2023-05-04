#!/bin/bash 

git filter-branch  --force --env-filter ' 
     if [ "$GIT_COMMITTER_NAME" = "mutualdropship" ] || [ "$GIT_AUTHOR_EMAIL" = "mutualtradecn@gmail.com" ]; 
     then 
        #替换用户名为新的用户名，替换邮箱为正确的邮箱
        GIT_AUTHOR_NAME="mutualdropship"; 
        GIT_AUTHOR_EMAIL="mutualtradecn@gmail.com"; 

        #替换提交的用户名为新的用户名，替换提交的邮箱为正确的邮箱
        GIT_COMMITTER_NAME="kevins"; 
        GIT_COMMITTER_EMAIL="kevins@qq.com"; 
     fi 
'  --tag-name-filter cat -- --branches --tags
