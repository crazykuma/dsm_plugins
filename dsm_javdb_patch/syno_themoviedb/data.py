#!/usr/bin/python2
#-*- coding:utf8 -*-
# By sanyle 2018-05-03

import requests,sys,os,time
from bs4 import BeautifulSoup
import json
import urllib

reload(sys)
sys.setdefaultencoding('utf8')

def getvalues(item):
    values = []
    list = item.select("a")
    for i in range(len(list)):
        values.append(list[i].text)
    return values

def getdata(vid):
    url = "https://javdb4.com/v/"+vid
    r = requests.get(url)
    r = r.content.decode("utf-8")
    soup = BeautifulSoup(r,'lxml')
    list = soup.select(".panel-block")
    rt = {}
    rt["id"] = vid
    for i in range(len(list)-1):
        title=list[i].select("strong")[0].text
        if title == '类别':
            rt["genres"] = getvalues(list[i])
        elif title == '時間':
            rt["release_date"] = list[i].select('.value')[0].text
        elif title == '導演':
            rt["directors"] = getvalues(list[i])
        elif title == '演員':
            rt["actors"] = getvalues(list[i])
        elif title == '發行':
            rt["writers"] = getvalues(list[i])
    
    rt["summary"]=''
    rt['backdrop'] = soup.select('.video-cover')[0].get("src")
    if rt['backdrop'].find("http")==-1:
            rt['backdrop']="https:"+rt['backdrop']

    data = json.dumps(rt, ensure_ascii=False)
    return data

def main(argv):

    vid = argv[0]
    data = getdata(vid)
    print (data)

if __name__ == '__main__':
    main(sys.argv[1:])