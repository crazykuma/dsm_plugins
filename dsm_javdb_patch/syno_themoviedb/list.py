#!/usr/bin/python2
# -*- coding:utf8 -*-
# Created By sanyle 2018-05-03
# Edited By crazykuma 2020-05-09

import requests
import sys
import json
from bs4 import BeautifulSoup

reload(sys)
sys.setdefaultencoding('utf8')


def javlist(title):
    url = "https://javdb4.com/search?f=all&q="
    r = requests.get(url+title)
    r = r.content.decode("utf-8")
    soup = BeautifulSoup(r, "lxml")
    res = soup.select("#videos .column")
    result = {}
    listnum = len(res)
    result['total'] = listnum
    data = []
    for i in range(len(res)):
        if i > 3:
            break
        vmsg = {}
        id = res[i].select("a")[0].get('href').replace("/v/", "")
        title = res[i].select(".uid")[0].text.strip()
        sub_title = res[i].select(".video-title")[0].text.strip()
        poster = res[i].select(".item-image > img")[0].get("data-src")
        if poster.find("http") == -1:
            poster = "https:"+poster
        vmsg['id'] = id
        vmsg['title'] = title
        vmsg['sub_title'] = sub_title
        vmsg['subtype'] = 'movie'
        vmsg['lang'] = 'jpn'
        vmsg['poster'] = poster
        data.append(vmsg)

    result['data'] = data
    return result


def main(argv):
    title = "".join(argv).replace('BD', '').replace('HD', '').replace('：', ':')
    arr = javlist(title)
    data = json.dumps(arr, ensure_ascii=False)
    print(data)


if __name__ == '__main__':
    main(sys.argv[1:])
