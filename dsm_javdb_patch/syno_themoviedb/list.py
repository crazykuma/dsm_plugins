#!/usr/bin/python2
#-*- coding:utf8 -*-
# By sanyle 2018-05-03

import requests
import re,sys,json,os
from bs4 import BeautifulSoup

reload(sys)
sys.setdefaultencoding('utf8')

def javlist(title):
    url = "https://javdb4.com/search?f=all&q="
    r = requests.get(url+title)
    r = r.content.decode("utf-8")
    soup = BeautifulSoup(r,"lxml")
    list = soup.select("#videos .column")
    json = {}
    listnum = len(list)
    json['total'] = listnum
    data = []
    for i in range(len(list)):
        if i>3:
            break
        vmsg = {}
        id = list[i].select("a")[0].get('href').replace("/v/","")
        title = list[i].select(".uid")[0].text.strip()
        sub_title = list[i].select(".video-title")[0].text.strip()
        poster = list[i].select(".item-image > img")[0].get("src")
        if poster.find("http")==-1:
            poster="https:"+poster
        vmsg['id'] = id
        vmsg['title'] = title
        vmsg['sub_title'] = sub_title
        vmsg['subtype'] = 'movie'
        vmsg['lang'] = 'jpn'
        vmsg['poster'] = poster
        data.append(vmsg)

    json['data'] = data
    return json

def main(argv):
    title = "".join(argv).replace('BD','').replace('HD','').replace('：',':')
    list =  javlist(title)
    data = json.dumps(list, ensure_ascii=False)
    print(data)
if __name__ == '__main__':
    main(sys.argv[1:])