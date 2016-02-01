import time
from splinter import Browser
import sys
import datetime as dt
import os
clear = lambda: os.system('cls')
clear()

with Browser() as browser:
    n1 = dt.datetime.now()
    username = "<USERNAME>"
    password = "<PASSWORD>"
    #username = input("7thCav email ?")
    #password = input("7thCav password?")
    id = input("7thCav OLD MILPAC ID?")
    newId = input("7Cav NEW MILPAC ID?")
    url = "http://backup.7thcavalry.us/index.php?app=core&module=global&section=login"
    browser.visit(url)
    browser.find_by_id('ips_username').fill(username)
    browser.find_by_id('ips_password').fill(password)
    browser.find_by_css('.input_submit').first.click()
    browser.visit("http://backup.7thcavalry.us/milpacs/soldierprofile.php?uniqueid=" + str(id))
    dates = []
    records = []
    tables = browser.find_by_css(".row2").find_by_tag("table")
    name = browser.find_by_xpath('//*[@id="content"]/div[3]/table[2]/tbody/tr[1]/td/b/font').first.text
    print("---OLD PROFILE---")
    print(name)
    print("-----------------")
    for table in tables:
        if(len(table.find_by_tag("td")) == 2):
            dates.append( table.find_by_tag("td").find_by_tag("font").first.text)
            string = table.find_by_tag("td").last.text
            if(string.find(" (citation)") != -1):
                string = string.replace(" (citation)", "")
            if(string.find(" (Citation)") != -1):
                string = string.replace(" (Citation)", "")
            records.append(' ' + string)
    totalRecords = len(dates)
    print("Found " + str(totalRecords) + " records for user ID: " + str(id))
    print("Copying data has completed.")
    print("Going to new 7Cav Website")


    browser.visit("https://7cav.us/rosters/profile?uniqueid=" + str(newId))
    browser.find_by_xpath('//*[@id="navigation"]/div/nav/div/ul[2]/li/label/a').click()
    time.sleep(1)

    #Log in to new website
    browser.find_by_id("LoginControl").fill(username)
    browser.find_by_id("ctrl_password").fill(password)
    browser.find_by_xpath('//*[@id="login"]/div/dl[3]/dd/input').click()
    print("---NEW PROFILE---")
    print(browser.find_by_css('.titleBar').text)
    print("-----------------")
    print("Make sure the new MILPAC is EMPTY. There can not be ANY Service Records. AND")
    confirm = input("Do these two profiles match? Enter n to stop program! Press ENTER to continue")
    if(confirm == "n" or confirm == "N"):
        sys.exit("Profiles were not the same. Shutting down")

    clear()
    #Loop times
    for x in range(0, len(dates)):
    #for x in range(0, 2):
        #Button --> Add new record
        browser.find_by_xpath('//*[@id="content"]/div/div/div[1]/div/a[4]').click()
        print("Record: " + str(x))
        print("Entering record " + records[x])
        print("Entering record with date: " + dates[x])
        print("------")
        #Fill in date
        browser.find_by_id('ctrl_record_date').click()
        javascriptcode2 = '$(document).unbind("keydown");'
        javascriptcode = '$("#ctrl_record_date").unbind("keydown");'
        browser.execute_script(javascriptcode2)
        browser.execute_script(javascriptcode)
        browser.find_by_id('ctrl_record_date').fill(dates[x])


        #Fill in record
        with browser.get_iframe(0) as iframe:
            iframe.find_by_xpath('/html/body/p').fill(records[x])

        #Click Save Changes
        browser.find_by_xpath('//*[@id="content"]/div/div/form/dl/dd/input').click()
    n2 = dt.datetime.now()
    print("Copying has finished in ")
    print((n2-n1).total_seconds())
    print("seconds, for " + str(totalRecords) + " records.")
    print("Programmed by CPL.Sadones.J")
    input("Press any key to close this window.")
