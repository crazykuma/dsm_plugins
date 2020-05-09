#!/usr/bin/python2
# -*- coding:utf8 -*-
# Created By sanyle 2018-05-03
# Edited by crazykuma 2020-05-09

import requests
import sys
from bs4 import BeautifulSoup
import json

reload(sys)
sys.setdefaultencoding('utf8')


def getvalues(item):
    values = []
    arr = item.select("a")
    for i in range(len(arr)):
        values.append(arr[i].text)
    return values


def getdata(vid):
    url = "https://javdb4.com/v/"+vid
    r = requests.get(url)
    r = r.content.decode("utf-8")
    soup = BeautifulSoup(r, 'lxml')
    res = soup.select(".panel-block")
    rt = {}
    rt["id"] = vid

    for i in range(len(res)-1):
        title = res[i].select("strong")[0].text[:-1]
        if title == '類別':
            rt["genres"] = getvalues(res[i])
        elif title == '時間':
            rt["release_date"] = res[i].select('.value')[0].text
        elif title == '導演':
            rt["directors"] = getvalues(res[i])
        elif title == '演員':
            rt["actors"] = getvalues(res[i])
        elif title == '片商':
            rt["writers"] = getvalues(res[i])
        elif title == '評分':
            rt['vote_average'] = float(res[i].select(
                ".value")[0].text.split("分")[0].split()[0])

    rt["summary"] = ''
    rt['backdrop'] = soup.select('.video-cover')[0].get("src")
    if rt['backdrop'].find("http") == -1:
        rt['backdrop'] = "https:"+rt['backdrop']

    data = json.dumps(rt, ensure_ascii=False)
    return data


def main(argv):

    vid = argv[0]
    data = getdata(vid)
    print (data)


if __name__ == '__main__':
    main(sys.argv[1:])
