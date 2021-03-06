# =============================================
# File: httpClient.py
# Author: Benny Saxen
# Date: 2019-05-15
# Description: Mercury Client
# =============================================
from lib import *
#===================================================
# Setup
#===================================================
confile = "httpclient.conf"
version = 1
lib_setup(co,confile,version)
#===================================================
# Loop
#===================================================
while True:
    lib_increaseMyCounter(co,dy)

    msg = lib_publishMyMeta(co,dy)

    payload = '{"test":"10043","test2": "453"}'
    print payload
    msg = lib_publishMyPayload(co,dy,payload)

    lib_commonAction(co,msg)

    message = 'counter:' + str(dy.mycounter)
    lib_publishMyLog(co, message)

    print "sleep: " + str(co.myperiod) + " triggered: " + str(dy.mycounter)
    time.sleep(float(co.myperiod))
#===================================================
# End of file
#===================================================
