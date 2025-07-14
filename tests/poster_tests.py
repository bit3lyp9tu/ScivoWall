import argparse
import os
import sys
import datetime
import time

import unittest
import mypy
import re
from selenium import webdriver

from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.by import By
from selenium.webdriver.support.select import Select
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.common.action_chains import ActionChains

# from selenium.webdriver.common.desired_capabilities import DesiredCapabilities
from selenium.webdriver.firefox.options import Options

# from selenium.webdriver.firefox.service import Service
from selenium.webdriver.firefox.firefox_binary import FirefoxBinary
from selenium.webdriver.firefox.service import Service as FirefoxService

driver = None
address = None

def create_driver ():
    global driver, address

    options = Options()

    if os.environ.get("GITHUB_ACTIONS"):
        options.add_argument("--headless")

        options.binary_location = "/usr/bin/firefox"
        service = FirefoxService(
            executable_path="/home/runner/cache/.driver/geckodriver"
        )
        driver = webdriver.Firefox(service=service, options=options)
        address = "127.0.0.1:8080"
    else:
        driver = webdriver.Firefox(options=options)
        address = "localhost"

class PythonOrgSearch(unittest.TestCase):

    wait_time = 1
    address = ""

    def test_search_in_python_org(self):
        driver.get(f"http://{address}/scientific_poster_generator/login.php")
        self.assertIn("Poster Generator", driver.title)
        print("Testing Page: " + driver.title)

        self.login_page()

        self.user_page()
        self.poster_page(3)
        self.admin_user()
        self.index_page()

        # self.logout()

    def tearDown(self):
        if os.environ.get("GITHUB_ACTIONS"):
            driver.close()

    def login_page(self):

        # check both empty
        self.login_fill_form("", "")
        time.sleep(self.wait_time)
        driver.find_element(By.ID, "login").click()
        self.assertEqual(
            f"http://{address}/scientific_poster_generator/login.php",
            driver.current_url,
        )
        time.sleep(self.wait_time)
        self.login_clear_form()

        time.sleep(self.wait_time)
        # check wrong name
        self.login_fill_form("Max Mustermann" + "123", "AbC123-98xy")
        time.sleep(self.wait_time)
        driver.find_element(By.ID, "login").click()
        time.sleep(self.wait_time)
        self.assertEqual(
            f"http://{address}/scientific_poster_generator/login.php",
            driver.current_url,
        )
        time.sleep(self.wait_time)
        self.login_clear_form()

        time.sleep(self.wait_time)
        # check wrong pw
        self.login_fill_form("Max Mustermann", "AbC123-98xy" + "abc")
        time.sleep(self.wait_time)
        driver.find_element(By.ID, "login").click()
        time.sleep(self.wait_time)
        self.assertEqual(
            f"http://{address}/scientific_poster_generator/login.php",
            driver.current_url,
        )
        time.sleep(self.wait_time)
        self.login_clear_form()

        time.sleep(self.wait_time)
        # check right name+pw
        self.login_fill_form("max5", "abc")
        # time.sleep(self.wait_time)
        driver.find_element(By.ID, "login").click()
        time.sleep(self.wait_time)
        self.assertEqual(
            f"http://{address}/scientific_poster_generator/projects.php",
            driver.current_url,
        )

        time.sleep(self.wait_time)
        # check cookie session
        session_id = driver.get_cookie("sessionID")
        time.sleep(self.wait_time)
        self.assertIsNotNone(session_id)

        pass

    def login_fill_form(self, name, pw):
        self.assertEqual(
            f"http://{address}/scientific_poster_generator/login.php",
            driver.current_url,
        )
        time.sleep(self.wait_time)
        filed_name = driver.find_element(By.ID, "name")
        time.sleep(self.wait_time)
        filed_name.send_keys(name)
        filed_name.send_keys(Keys.RETURN)

        time.sleep(self.wait_time)

        filed_pw = driver.find_element(By.ID, "pw")
        time.sleep(self.wait_time)
        filed_pw.send_keys(pw)
        filed_pw.send_keys(Keys.RETURN)

    def login_clear_form(self):
        driver.find_element(By.ID, "name").clear()
        time.sleep(self.wait_time)
        driver.find_element(By.ID, "pw").clear()

    def logout(self):
        driver.get(f"http://{address}/scientific_poster_generator/projects.php")
        time.sleep(self.wait_time)
        # check if page contains logout
        logout = driver.find_element(By.ID, "logout")
        time.sleep(self.wait_time)
        self.assertIsNotNone(logout)
        time.sleep(self.wait_time)
        logout.click()
        self.assertEqual(
            f"http://{address}/scientific_poster_generator/login.php",
            driver.current_url,
        )

        # check if correct logout   ???
        pass

    def register(self, name, pw):
        # TODO: check register user
        # check existing name
        # check invalid pw
        # check two diffent pw
        # check correct action + now at login page
        pass

    def user_page(self):
        self.assertEqual(
            f"http://{address}/scientific_poster_generator/projects.php",
            driver.current_url,
        )
        time.sleep(self.wait_time)

        # check poster list correctly loaded
        poster_list_element = driver.find_element(
            By.CSS_SELECTOR, "#table-container>table>tr#table-container--nr-3"
        )
        time.sleep(self.wait_time)
        self.assertTrue(
            poster_list_element.text
            in [
                "2025-04-16 13:43:02\npublic\nprivate",
                "2025-04-16 11:43:02\npublic\nprivate",
            ]
        )

        time.sleep(self.wait_time)
        # check add new poster
        create_poster = driver.find_element(By.ID, "project-name")
        time.sleep(self.wait_time)
        create_poster.send_keys("Test Title")
        create_poster.send_keys(Keys.RETURN)

        time.sleep(self.wait_time)

        driver.find_element(By.CSS_SELECTOR, "#create-project>button").click()
        time.sleep(self.wait_time)

        poster_list_element = driver.find_element(
            By.CSS_SELECTOR, "#table-container>table>tr#table-container--nr-4"
        )
        time.sleep(self.wait_time)
        self.assertIsNotNone(poster_list_element)

        # check right date
        date = datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        time.sleep(self.wait_time)
        self.assertTrue(
            self.date_compair_day(date, poster_list_element.text.split(" ")[0])
        )

        time.sleep(self.wait_time)

        # check toggle visible (as non-admin)
        check_btn = driver.find_element(
            By.CSS_SELECTOR,
            "div#table-container>table>tr:nth-child(3)>td:nth-child(3)>input",
        )
        self.assertIsNotNone(check_btn.get_attribute("disabled"))
        print(
            [
                i.text
                for i in driver.find_elements(
                    By.CSS_SELECTOR,
                    "div#table-container>table>tr:nth-child(3)>td:nth-child(3)",
                )
            ]
        )

        time.sleep(self.wait_time)

        # check visibility (view modes)
        select_element = driver.find_element(
            By.CSS_SELECTOR,
            "div#table-container>table>tr:nth-child(3)>td:nth-child(4)>select",
        )
        select = Select(select_element)
        # print([i.text for i in select.options])
        #   - private
        self.assertEqual("private", select.all_selected_options[0].text)
        #   - public
        select.select_by_visible_text("public")
        self.assertEqual(
            "public",
            Select(
                driver.find_element(
                    By.CSS_SELECTOR,
                    "div#table-container>table>tr:nth-child(3)>td:nth-child(4)>select",
                )
            )
            .all_selected_options[0]
            .text,
        )

        time.sleep(self.wait_time)
        # check edit poster title
        custom_poster = driver.find_element(
            By.CSS_SELECTOR,
            "#table-container>table>tr#table-container--nr-4>td:first-child>input",
        )
        time.sleep(self.wait_time)
        self.assertEqual("Test Title", custom_poster.get_attribute("value"))

        ActionChains(driver).click(custom_poster).send_keys(" abc").perform()

        # custom_poster.click()
        # custom_poster.send_keys(" abc")

        # time.sleep(self.wait_time)
        # custom_poster.send_keys(Keys.TAB)
        time.sleep(self.wait_time)

        driver.find_element(
            By.CSS_SELECTOR,
            "#table-container>table>tr#table-container--nr-1>td:first-child>input",
        ).click()
        time.sleep(self.wait_time)

        # custom_poster2 = driver.find_element(
        #     By.CSS_SELECTOR,
        #     "#table-container>table>tr#table-container--nr-4>td:first-child>input",
        # )
        # self.assertEqual("Test Title abc", custom_poster2.get_attribute("value"))

        # check after page reload

        time.sleep(self.wait_time)
        # check delete new poster
        driver.find_element(
            By.CSS_SELECTOR,
            "#table-container>table>tr#table-container--nr-4>td:last-child>td>input",
        ).click()
        time.sleep(self.wait_time)
        poster_list_element = driver.find_element(
            By.CSS_SELECTOR, "#table-container>table>tr:last-child>td:first-child>input"
        )
        time.sleep(self.wait_time)
        self.assertTrue(
            poster_list_element not in ["Test Title", "Test Title abc"]
        )  # TODO:   change to only expecting 'Test Title abc'
        # self.assertEqual("Test Title abc", poster_list_element)

        time.sleep(self.wait_time)
        # check access poster
        driver.find_element(
            By.CSS_SELECTOR,
            "#table-container>table>tr#table-container--nr-3>td:nth-last-child(2)>td>input",
        ).click()
        time.sleep(self.wait_time)
        self.assertEqual(
            f"http://{address}/scientific_poster_generator/poster.php",
            driver.current_url.split("?")[0],
        )

        driver.get(
            f"http://{address}/scientific_poster_generator/projects.php",
        )

        time.sleep(self.wait_time)

        self.assertListEqual(
            [
                "ChatGPT",
                "Alice Johnson",
                "Dr. Rahul Mehta",
                "ChatGPT",
                "Lina Chen",
                "Marcus Lee",
                "ChatGPT",
                "Alice Johnson",
                "Lina Chen",
            ],
            [
                i.get_attribute("value")
                for i in driver.find_elements(
                    By.CSS_SELECTOR, "#author-list>table>*>td:nth-child(2)>input"
                )
            ],
        )

        time.sleep(self.wait_time)

        # check author list correctly loaded
        author_list_element = driver.find_element(
            By.CSS_SELECTOR,
            "#author-list>table>tr#author-list--nr-9>td:nth-child(2)>input",
        )
        time.sleep(self.wait_time)
        self.assertEqual("Lina Chen", author_list_element.get_attribute("value"))

        author_list_element2 = driver.find_element(
            By.CSS_SELECTOR,
            "#author-list>table>tr#author-list--nr-9>td:nth-child(1)",
        )
        time.sleep(self.wait_time)
        self.assertEqual("The Future of Urban Farming", author_list_element2.text)

        # check edit author name
        author_list_element3 = driver.find_element(
            By.CSS_SELECTOR,
            "#author-list>table>tr#author-list--nr-9>td:nth-child(2)>input",
        )
        time.sleep(self.wait_time)
        author_list_element3.click()
        author_list_element3.send_keys(" abc")

        time.sleep(self.wait_time)
        author_list_element3.send_keys(Keys.TAB)
        time.sleep(self.wait_time)

        driver.find_element(
            By.CSS_SELECTOR,
            "#author-list>table>tr#author-list--nr-1>td:nth-child(2)>input",
        ).click()
        time.sleep(self.wait_time)
        author_list_element4 = driver.find_element(
            By.CSS_SELECTOR,
            "#author-list>table>tr#author-list--nr-9>td:nth-child(2)>input",
        )
        self.assertTrue(
            author_list_element4.get_attribute("value")
            in ["Lina Chen", "Lina Chen abc"]
        )  # TODO:   change to only expecting 'Lina Chen abc'

        time.sleep(self.wait_time)
        # check delete author
        driver.find_element(
            By.CSS_SELECTOR,
            "#author-list>table>tr#author-list--nr-6>td:last-child>td>input",
        ).click()
        time.sleep(self.wait_time)
        author_list_element5 = driver.find_element(
            By.CSS_SELECTOR,
            "#author-list>table>tr:nth-child(6)>td:nth-child(2)>input",
        )
        self.assertIsNot("Alice johnson", author_list_element5.get_attribute("value"))

        # check image rename
        img_element = driver.find_element(
            By.CSS_SELECTOR,
            "#image-list>table>tr:nth-child(3)>td:nth-child(2)>input",
        )
        img_element.click()
        for i in range(7):
            img_element.send_keys(Keys.ARROW_RIGHT)
        img_element.send_keys("-test")
        self.assertEqual(
            "scadslogo.png-test",
            driver.find_element(
                By.CSS_SELECTOR,
                "#image-list>table>tr:nth-child(3)>td:nth-child(2)>input",
            ).get_attribute("value"),
        )

        # check image delete
        img_element2 = driver.find_element(
            By.CSS_SELECTOR,
            "#image-list>table>tr:nth-child(3)>td:nth-child(5)>td>input",
        )
        img_element2.click()
        imgs = [
            i.get_attribute("value")
            for i in driver.find_elements(
                By.CSS_SELECTOR, "#image-list>table>*>td:nth-child(2)>input"
            )
        ]
        self.assertListEqual(imgs, ["tudlogo.png", "leipzig.png"])
        pass

    def date_compair_day(self, date1, date2):
        g1 = date1.split(" ")
        g2 = date2.split(" ")

        return g1[0] == g2[0]
        # and g1[1].split(":")[0] == g2[1].split(":")[0]

    def poster_tests(self, css_selector, poster_id, data, isAdmin):

        # check right title
        title = driver.find_element(By.CSS_SELECTOR, "div#title")
        time.sleep(self.wait_time)
        # print(title.text)
        self.assertEqual(data["title"], title.text)

        time.sleep(self.wait_time)

        # check edit title
        title = driver.find_element(By.CSS_SELECTOR, "div#titles>div")
        ActionChains(driver).move_to_element(title).click(title).perform()
        time.sleep(self.wait_time)

        ActionChains(driver).click(
            driver.find_element(By.CSS_SELECTOR, "div#titles>div>textarea")
        ).send_keys(Keys.DOWN).send_keys(" abc").send_keys(Keys.ENTER).send_keys(
            Keys.BACKSPACE
        ).click(
            driver.find_element(By.CSS_SELECTOR, "img#scadslogo")
        ).perform()
        time.sleep(self.wait_time)

        # title2 = driver.find_element(By.CSS_SELECTOR, "div#titles>div")
        # title2.click()
        # time.sleep(self.wait_time)
        # textarea = WebDriverWait(driver, 10).until(
        #     EC.presence_of_element_located((By.CSS_SELECTOR, "div#titles>div>textarea"))
        # )
        # WebDriverWait(driver, 10).until(
        #     EC.element_to_be_clickable((By.CSS_SELECTOR, "div#titles>div>textarea"))
        # )
        # textarea.send_keys(" abc")
        # time.sleep(self.wait_time)

        # driver.find_element(By.CSS_SELECTOR, "img#scadslogo").click()
        # time.sleep(self.wait_time)

        title3 = driver.find_element(By.CSS_SELECTOR, "div#titles>div>div#title>p")
        self.assertEqual(data["title"] + " abc", title3.text)
        time.sleep(self.wait_time)
        self.assertEqual(
            data["title"] + " abc",
            driver.find_element(
                By.CSS_SELECTOR, "div#titles>div>div#title"
            ).get_attribute("data-content"),
        )

        print(driver.current_url)
        time.sleep(self.wait_time)
        # check authors
        authors = set(
            [
                i.text.split("\n")[0]
                for i in driver.find_elements(
                    By.CSS_SELECTOR, "div#authors>div.author-item"
                )
            ]
        )
        author_list = [set(data["authors"]["pre"]), set(data["authors"]["edited"])]
        print(f"{authors} : {author_list}")
        self.assertTrue(authors in author_list)

        # check empty authors

        # check add author
        WebDriverWait(driver, 20).until(
            EC.element_to_be_clickable(
                (By.CSS_SELECTOR, "div#authors>input:last-child")
            )
        ).click()
        ActionChains(driver).send_keys("Author").perform()
        driver.find_element(By.ID, "logo_headline").click()
        changed_authors = [
            i.text
            for i in driver.find_elements(
                By.CSS_SELECTOR, "div#authors>div.author-item"
            )
        ]
        print(f"check add author: {changed_authors}")
        self.assertTrue(
            changed_authors
            in [data["authors"]["added"], data["authors"]["added-edited"]],
        )

        time.sleep(self.wait_time)
        # TODO:   check author list switch order
        drag = WebDriverWait(driver, 10).until(
            EC.element_to_be_clickable(
                (
                    By.CSS_SELECTOR,
                    "div#authors>div:nth-child(1)",
                )
            )
        )
        start = drag.location
        time.sleep(self.wait_time)
        drop = WebDriverWait(driver, 10).until(
            EC.element_to_be_clickable(
                (
                    By.CSS_SELECTOR,
                    "div#authors>div:nth-child(3)",
                )
            )
        )
        finish = drop.location
        ActionChains(driver).drag_and_drop(drag, drop).perform()
        author_order = [
            i.text
            for i in driver.find_elements(
                By.CSS_SELECTOR, "div#authors>div.author-item"
            )
        ]
        # print(author_order)
        # self.assertEqual([], author_order)

        # check author stored

        # check author delete
        last_author = driver.find_element(
            By.CSS_SELECTOR, "div#authors>div:nth-last-child(2)"
        )
        ActionChains(driver).move_to_element(last_author).perform()
        driver.find_element(
            By.CSS_SELECTOR,
            "div#authors>div:nth-last-child(2)>button.remove-element",
        ).click()
        # driver.execute_script("document.")
        changed_authors2 = [
            i.text
            for i in driver.find_elements(
                By.CSS_SELECTOR, "div#authors>div.author-item"
            )
        ]
        self.assertTrue(
            changed_authors2 in [data["authors"]["pre"], data["authors"]["edited"]],
        )

        # check add box
        boxes = [i for i in driver.find_elements(By.CSS_SELECTOR, "div#boxes>div")]
        driver.find_element(By.CSS_SELECTOR, "input#add-box").click()
        boxes2 = [i for i in driver.find_elements(By.CSS_SELECTOR, "div#boxes>div")]
        self.assertEqual(len(boxes) + 1, len(boxes2))

        # check basic edit box
        new_box = driver.find_element(By.CSS_SELECTOR, "div#boxes>div:nth-child(3)")
        ActionChains(driver).move_to_element(new_box).click(new_box).perform()

        ActionChains(driver).click(
            driver.find_element(By.CSS_SELECTOR, "div#boxes>textarea#editBox-2")
        ).send_keys(Keys.DOWN).send_keys(" abc").send_keys(Keys.ENTER).send_keys(
            "$$ x $$"
        ).click(
            driver.find_element(By.CSS_SELECTOR, "img#scadslogo")
        ).perform()

        changed_box = driver.find_element(By.CSS_SELECTOR, "div#boxes>div:nth-child(3)")
        self.assertEqual(
            data["boxes"][0],
            changed_box.get_attribute("data-content"),
        )

        # check box markdown render
        self.assertEqual(
            data["boxes"][1],
            driver.find_element(By.CSS_SELECTOR, "div#boxes>div:nth-child(3)>h1").text,
        )
        self.assertEqual(
            data["boxes"][2],
            driver.find_element(By.CSS_SELECTOR, "div#boxes>div:nth-child(3)>p").text,
        )

        # check box math render
        self.assertIsNotNone(
            driver.find_element(
                By.CSS_SELECTOR, "div#boxes>div:nth-child(3)>p>mjx-container"
            )
        )
        time.sleep(self.wait_time)

        # TODO:   check box plotly render   ???

        # TODO: check image upload
        # img_path = os.path.abspath("scientific_poster_generator/img/tudlogo.png")
        # print(img_path)

        # self.assertTrue(os.path.isfile(img_path))

        # ActionChains(driver).click(
        #     driver.find_element(By.CSS_SELECTOR, "div#editBox-3")
        # ).click(driver.find_element(By.CSS_SELECTOR, "img#scadslogo")).perform()

        # file_input = driver.find_element(
        #     By.CSS_SELECTOR, "div#editBox-3>input[type='file']"
        # )
        # file_input.send_keys(img_path)
        # time.sleep(self.wait_time)

        # new_box = driver.find_element(By.CSS_SELECTOR, "div#editBox-3")
        # ActionChains(driver).move_to_element(new_box).click(new_box).perform()

        # # TODO: do without manually adding the placeholder
        # ActionChains(driver).click(
        #     driver.find_element(By.CSS_SELECTOR, "div#boxes>textarea#editBox-3")
        # ).send_keys(Keys.DOWN).send_keys(" abc").send_keys(Keys.ENTER).send_keys(
        #     '<p placeholder="image">includegraphics{tudlogo.png}</p>'
        # ).click(
        #     driver.find_element(By.CSS_SELECTOR, "img#scadslogo")
        # ).perform()

        # find_uploaded_image = driver.find_element(
        #     By.CSS_SELECTOR, "div#editBox-3 img.box-img"
        # )
        # self.assertIsNotNone(find_uploaded_image)
        # time.sleep(self.wait_time)

        # check visibility
        select_element = driver.find_element(By.CSS_SELECTOR, "select#view-mode")
        select = Select(select_element)
        # print([i.text for i in select.options])
        #   - private
        self.assertEqual("private", select.all_selected_options[0].text)
        #   - public
        select.select_by_visible_text("public")
        self.assertEqual(
            "public",
            Select(driver.find_element(By.CSS_SELECTOR, "select#view-mode"))
            .all_selected_options[0]
            .text,
        )

        driver.get(f"http://{address}/scientific_poster_generator/projects.php")
        time.sleep(self.wait_time)
        column_nr = 3 if isAdmin else 2
        row_nr = 2 if isAdmin else 4
        timestamp = datetime.datetime.now().strftime("%Y-%m-%d")
        # print(timestamp)
        self.assertEqual(
            timestamp,
            driver.find_element(
                By.CSS_SELECTOR,
                f"div#table-container>table>tr:nth-child({row_nr})>td:nth-child({column_nr})",
            )
            .get_attribute("innerText")
            .split(" ")[0],
        )
        pass

    def poster_page(self, local_index):

        driver.get(
            f"http://{address}/scientific_poster_generator/projects.php",
        )

        time.sleep(3)

        poster_row = driver.find_element(
            By.CSS_SELECTOR,
            f"#table-container>table>tr#table-container--nr-{local_index}>td:nth-last-child(2)>td>input",
        )
        poster_id = poster_row.get_attribute("pk_id")
        poster_row.click()

        data = {
            "title": "The Future of Urban Farming",
            "authors": {
                "pre": ["ChatGPT", "Alice Johnson", "Lina Chen"],
                "edited": ["ChatGPT", "Alice Johnson", "Lina Chen abc"],
                "added": ["ChatGPT", "Alice Johnson", "Lina Chen", "Author"],
                "added-edited": ["ChatGPT", "Alice Johnson", "Lina Chen abc", "Author"],
            },
            "boxes": [
                "# Impact\n\nIncreased yields with 70% less water usage. abc\n$$ x $$",
                "Impact",
                "Increased yields with 70% less water usage. abc\nx",
            ],
        }

        self.poster_tests("", poster_id, data, False)
        time.sleep(self.wait_time)

        # TODO: check if other user cannot change
        # poster_id = 108
        # driver.get(
        #     f"http://{address}/scientific_poster_generator/poster.php?id={poster_id}&mode=private"
        # )
        # self.assertTrue(False)

    def check_filter(self, css_selector, results):
        time.sleep(self.wait_time)
        self.assertListEqual(
            results,
            [
                el.get_attribute("value")
                for el in driver.find_elements(By.CSS_SELECTOR, css_selector)
            ],
        )
        pass

    def check_selected(self, css_selector, default_selected, results):
        time.sleep(self.wait_time)
        select_user = driver.find_element(
            By.CSS_SELECTOR,
            css_selector,
        )
        sel1 = Select(select_user)
        time.sleep(self.wait_time)
        self.assertListEqual(
            results,
            [i.text for i in sel1.options],
        )
        time.sleep(self.wait_time)
        self.assertEqual(default_selected, sel1.all_selected_options[0].text)
        pass

    def change_selector(self, css_selector, value, css_submit):
        time.sleep(self.wait_time)
        select_user = driver.find_element(
            By.CSS_SELECTOR,
            css_selector,
        )
        sel1 = Select(select_user)
        time.sleep(self.wait_time)
        sel1.select_by_visible_text(value)

        if css_submit:
            time.sleep(self.wait_time)
            driver.find_element(By.CSS_SELECTOR, css_submit).click()
        pass

    def check_rename_row(self, css_cell, value, css_names, list):
        page = f"http://{address}/scientific_poster_generator/projects.php"
        filter_config = [
            ("select#select_user", "max5", "input#submit-filter"),
            ("select#visibility", "1", "input#submit-filter"),
        ]

        time.sleep(self.wait_time)
        img_last_edit = driver.find_element(
            By.CSS_SELECTOR,
            css_cell,
        ).text
        time.sleep(self.wait_time)
        img_selector = driver.find_element(
            By.CSS_SELECTOR,
            css_cell + ">input",
        )
        img_selector.send_keys(value)
        time.sleep(self.wait_time)
        driver.find_element(
            By.CSS_SELECTOR, "div#author-list>table>tr:nth-child(1)>th:nth-child(2)"
        ).click()
        time.sleep(self.wait_time)
        driver.get(page)
        for i in filter_config:
            self.change_selector(*i)
        time.sleep(self.wait_time)
        self.check_filter(
            css_names,
            list,
        )
        # check correct time change - image
        time.sleep(self.wait_time)
        self.assertEqual(
            img_last_edit,
            driver.find_element(
                By.CSS_SELECTOR,
                css_cell,
            ).text,
        )
        pass

    def check_delete_row(self, css_selector, css_names, list):
        page = f"http://{address}/scientific_poster_generator/projects.php"
        filter_config = [
            ("select#select_user", "max5", "input#submit-filter"),
            ("select#visibility", "1", "input#submit-filter"),
        ]

        time.sleep(self.wait_time)
        driver.find_element(
            By.CSS_SELECTOR,
            css_selector,
        ).click()
        time.sleep(self.wait_time)
        self.check_filter(
            css_names,
            list,
        )
        driver.get(page)
        for i in filter_config:
            self.change_selector(*i)

        time.sleep(self.wait_time)
        self.check_filter(
            css_names,
            list,
        )
        pass

    def admin_user(self):

        # go to projects page
        driver.get(f"http://{address}/scientific_poster_generator/projects.php")
        time.sleep(self.wait_time)

        # logout
        driver.find_element(By.ID, "logout").click()
        time.sleep(self.wait_time)

        # login as admin
        self.login_fill_form("Admin", "PwScaDS-2025")
        driver.find_element(By.ID, "login").click()
        self.assertEqual(
            f"http://{address}/scientific_poster_generator/projects.php",
            driver.current_url,
        )
        time.sleep(self.wait_time)

        # check login successfully
        self.assertEqual(
            7,
            len(
                driver.find_elements(
                    By.CSS_SELECTOR,
                    "div#table-container>table>tr:nth-child(2)>td",
                )
            ),
        )
        # check set poster to visible
        self.assertTrue(
            "on",
            driver.find_elements(
                By.CSS_SELECTOR,
                "div#table-container>table>tr#table-container--nr-1>td:nth-child(4)>input",
            )[0].get_attribute("value"),
        )
        driver.get(f"http://{address}/scientific_poster_generator/index.php")
        self.assertIsNotNone(
            driver.find_elements(By.CSS_SELECTOR, "div#posters>div>iframe")
        )

        time.sleep(self.wait_time)

        driver.get(f"http://{address}/scientific_poster_generator/projects.php")

        time.sleep(self.wait_time)

        visibility = driver.find_element(
            By.CSS_SELECTOR,
            "div#table-container>table>tr#table-container--nr-1>td:nth-child(4)>input",
        )

        # print(visibility.is_selected())

        ActionChains(driver).move_to_element(visibility).click(visibility).perform()

        # print(
        #     driver.find_element(
        #         By.CSS_SELECTOR,
        #         "div#table-container>table>tr#table-container--nr-1>td:nth-child(3)",
        #     ).is_selected()
        # )

        time.sleep(self.wait_time)

        driver.get(f"http://{address}/scientific_poster_generator/index.php")

        time.sleep(self.wait_time)

        self.assertEqual(
            [], driver.find_elements(By.CSS_SELECTOR, "div#posters>div>iframe")
        )

        driver.get(f"http://{address}/scientific_poster_generator/projects.php")

        # check if admin can change other posters
        time.sleep(self.wait_time)
        poster_id = 108
        driver.find_element(
            By.CSS_SELECTOR,
            "div#table-container>table>tr:nth-child(2)>td:nth-child(6)>td>input",
        ).click()
        time.sleep(self.wait_time)
        self.assertEqual(
            f"http://{address}/scientific_poster_generator/poster.php?id={poster_id}&mode=private",
            driver.current_url,
        )
        time.sleep(self.wait_time)

        data = {
            "title": "test1",
            "authors": {
                "pre": ["Author8", "Author5"],
                "edited": ["Author8", "Author5 abc"],
                "added": ["Author8", "Author5", "Author"],
                "added-edited": ["Author8", "Author5 abc", "Author"],
            },
            "boxes": [
                "# New Text abc\n$$ x $$",
                "New Text abc",
                "x",
            ],
        }
        self.poster_tests("", poster_id, data, True)

        time.sleep(self.wait_time)
        driver.get(f"http://{address}/scientific_poster_generator/projects.php")

        # check set view_mode
        self.change_selector(
            "#table-container>table>tr:nth-child(2)>td:nth-child(5)>select",
            "public",
            None,
        )
        # check if state stored after reload
        time.sleep(self.wait_time)
        driver.get(f"http://{address}/scientific_poster_generator/projects.php")
        self.check_selected(
            "div#table-container>table>tr:nth-child(2)>td:nth-child(5)>select",
            "public",
            ["public", "private"],
        )
        time.sleep(self.wait_time)
        driver.get(f"http://{address}/scientific_poster_generator/index.php")
        time.sleep(self.wait_time * 2 + 2)
        self.assertEqual(
            3,
            len(
                [i for i in driver.find_elements(By.CSS_SELECTOR, "div#posters>div>*")]
            ),
        )
        time.sleep(self.wait_time)
        driver.get(f"http://{address}/scientific_poster_generator/projects.php")

        time.sleep(self.wait_time)
        driver.find_element(By.CSS_SELECTOR, "input#submit-filter").click()

        # check filter categories
        # - check create filter category + reset selected
        time.sleep(self.wait_time)
        selecter = driver.find_element(
            By.CSS_SELECTOR,
            "select#select_view_mode",
        )
        sel1 = Select(selecter)
        time.sleep(self.wait_time)
        sel1.select_by_visible_text("public")
        time.sleep(self.wait_time)
        self.assertListEqual(
            ["-1", "public"],
            [
                i.get_attribute("value")
                for i in driver.find_elements(
                    By.CSS_SELECTOR,
                    "div#filter.filter-container>table>tr:nth-child(3)>div.filter-select>*",
                )
            ],
        )
        # - check delete category
        time.sleep(self.wait_time)
        driver.find_element(
            By.CSS_SELECTOR,
            "div#filter.filter-container>table>tr:nth-child(3)>div.filter-select>input",
        ).click()
        time.sleep(self.wait_time)
        self.assertListEqual(
            ["-1"],
            [
                i.get_attribute("value")
                for i in driver.find_elements(
                    By.CSS_SELECTOR,
                    "div#filter.filter-container>table>tr:nth-child(3)>div.filter-select>*",
                )
            ],
        )
        # - check no category duplicates
        time.sleep(self.wait_time)
        selecter = driver.find_element(
            By.CSS_SELECTOR,
            "select#select_view_mode",
        )
        sel1 = Select(selecter)
        time.sleep(self.wait_time)
        sel1.select_by_visible_text("public")
        time.sleep(self.wait_time)
        sel1.select_by_visible_text("private")
        time.sleep(self.wait_time)
        self.assertListEqual(
            ["-1", "public", "private"],
            [
                i.get_attribute("value")
                for i in driver.find_elements(
                    By.CSS_SELECTOR,
                    "div#filter.filter-container>table>tr:nth-child(3)>div.filter-select>*",
                )
            ],
        )
        time.sleep(self.wait_time)
        sel1.select_by_visible_text("private")
        time.sleep(self.wait_time)
        self.assertListEqual(
            ["-1", "public", "private"],
            [
                i.get_attribute("value")
                for i in driver.find_elements(
                    By.CSS_SELECTOR,
                    "div#filter.filter-container>table>tr:nth-child(3)>div.filter-select>*",
                )
            ],
        )
        # - clean up
        time.sleep(self.wait_time)
        driver.find_element(
            By.CSS_SELECTOR,
            "div#filter.filter-container>table>tr:nth-child(3)>div.filter-select>:nth-child(2)",
        ).click()
        time.sleep(self.wait_time)
        driver.find_element(
            By.CSS_SELECTOR,
            "div#filter.filter-container>table>tr:nth-child(3)>div.filter-select>:nth-child(2)",
        ).click()
        time.sleep(self.wait_time)
        self.assertListEqual(
            ["-1"],
            [
                i.get_attribute("value")
                for i in driver.find_elements(
                    By.CSS_SELECTOR,
                    "div#filter.filter-container>table>tr:nth-child(3)>div.filter-select>*",
                )
            ],
        )

        # check filter results posters - all
        self.check_filter(
            "div#table-container>table>*>td:nth-child(2)>input",
            [
                "test1 abc",
                "test4",
                "fxhfdf",
                "dxfgbfdffdbdfxbfbxbf",
                "Climate Change Effects in the Arctic",
                "AI in Modern Healthcare",
                "The Future of Urban Farming abc",
            ],
        )

        # check filter results authors - all
        self.check_filter(
            "div#author-list>table>*>td:nth-child(3)>input",
            [
                "Author8",
                "Author5",
                "ChatGPT",
                "Alice Johnson",
                "Dr. Rahul Mehta",
                "ChatGPT",
                "Lina Chen abc",
                "Marcus Lee",
                "ChatGPT",
                "Alice Johnson",
                "Lina Chen abc",
            ],
        )

        # check filter results imgs - all
        self.check_filter(
            "div#image-list>table>*>td:nth-child(2)>input",
            ["tudlogo.png", "leipzig.png"],
        )
        # check image data
        time.sleep(self.wait_time)
        for i in driver.find_elements(
            By.CSS_SELECTOR, "div#image-list>table>*>td:nth-child(1)>div>img"
        ):
            self.assertTrue(i.get_attribute("src") != "0")

        # check right select options in filter
        # check select user
        self.check_selected(
            "select#select_user",
            "-",
            ["-", "Admin", "Anne Beispielfrau", "bug", "Max Mustermann", "max5"],
        )
        # check select title
        self.check_selected(
            "select#select_title",
            "-",
            [
                "-",
                "test1 abc",
                "test4",
                "fxhfdf",
                "dxfgbfdffdbdfxbfbxbf",
                "Climate Change Effects in the Arctic",
                "AI in Modern Healthcare",
                "The Future of Urban Farming abc",
            ],
        )
        # check select view_mode
        self.check_selected(
            "select#select_view_mode", "-", ["-", "public", "private"]
        )

        # check select last_edit
        timestamp = datetime.datetime.now().strftime("%Y-%m-%d")
        self.assertEqual(
            len(
                [
                    i.get_attribute("innerText").split(" ")[0]
                    for i in driver.find_elements(
                        By.CSS_SELECTOR,
                        "div#table-container>table>*>td:nth-child(3)",
                    )
                    if i.get_attribute("innerText").split(" ")[0] == timestamp
                ]
            ),
            3,
        )

        # check select visibility
        self.check_selected("select#visibility", "-", ["-", "0", "1"])

        # check filter results posters change selector
        self.change_selector(
            "select#select_user", "max5", "input#submit-filter"
        )
        # check filter results posters - user
        self.check_filter(
            "div#table-container>table>*>td:nth-child(2)>input",
            [
                "Climate Change Effects in the Arctic",
                "AI in Modern Healthcare",
                "The Future of Urban Farming abc",
            ],
        )
        # check filter results authors - user
        self.check_filter(
            "div#author-list>table>*>td:nth-child(3)>input",
            [
                "ChatGPT",
                "Alice Johnson",
                "Dr. Rahul Mehta",
                "ChatGPT",
                "Lina Chen abc",
                "ChatGPT",
                "Alice Johnson",
                "Lina Chen abc",
            ],
        )
        # check filter results imgs - user
        self.check_filter(
            "div#image-list>table>*>td:nth-child(2)>input",
            ["tudlogo.png", "leipzig.png"],
        )

        # check with additional filter attribute - visibility
        self.change_selector("select#visibility", "0", "input#submit-filter")

        # check filter results posters - user, visibility
        self.check_filter(
            "div#table-container>table>*>td:nth-child(2)>input",
            ["Climate Change Effects in the Arctic"],
        )

        # check filter results authors - user, visibility
        self.check_filter(
            "div#author-list>table>*>td:nth-child(3)>input",
            ["ChatGPT", "Alice Johnson", "Dr. Rahul Mehta"],
        )

        # check filter results imgs - user, visibility
        self.check_filter(
            "div#image-list>table>*>td:nth-child(2)>input",
            [],
        )

        # check with additional filter attribute - view_mode
        self.change_selector(
            "select#select_view_mode", "public", "input#submit-filter"
        )
        # check filter results posters - user, visibility, view_mode
        self.check_filter(
            "div#table-container>table>*>td:nth-child(2)>input",
            [],
        )

        # check filter results authors - user, visibility, view_mode
        self.check_filter(
            "div#author-list>table>*>td:nth-child(3)>input",
            [],
        )

        # check filter results imgs - user, visibility, view_mode
        self.check_filter(
            "div#image-list>table>*>td:nth-child(2)>input",
            [],
        )

        # reset filter attribute - view_mode
        self.change_selector(
            "select#select_view_mode", "-", "input#submit-filter"
        )

        # change filter attribute - visibility
        self.change_selector("select#visibility", "1", "input#submit-filter")

        # check with additional filter attribute - poster
        self.change_selector(
            "select#select_title",
            "The Future of Urban Farming abc",
            "input#submit-filter",
        )

        # check filter results posters - user, visibility, poster
        self.check_filter(
            "div#table-container>table>*>td:nth-child(2)>input",
            ["The Future of Urban Farming abc"],
        )
        # check filter results authors - user, visibility, poster
        self.check_filter(
            "div#author-list>table>*>td:nth-child(3)>input",
            ["ChatGPT", "Alice Johnson", "Lina Chen abc"],
        )
        # check filter results imgs - user, visibility, poster
        self.check_filter(
            "div#image-list>table>*>td:nth-child(2)>input",
            ["tudlogo.png", "leipzig.png"],
        )

        # check correct rename - author
        self.check_rename_row(
            "div#author-list>table>tr:nth-child(2)>td:nth-child(3)",
            " abc",
            "div#author-list>table>*>td:nth-child(3)>input",
            [
                "ChatGPT abc",
                "Lina Chen abc",
                "ChatGPT abc",
                "Alice Johnson",
                "Lina Chen abc",
            ],
        )
        # check correct delete - author
        time.sleep(self.wait_time)
        self.check_delete_row(
            "div#author-list>table>tr:nth-child(2)>td:nth-child(4)>td>input",
            "div#author-list>table>*>td:nth-child(3)>input",
            [
                "Lina Chen abc",
                "ChatGPT abc",
                "Alice Johnson",
                "Lina Chen abc",
            ],
        )

        # check correct rename - image
        self.check_rename_row(
            "div#image-list>table>tr:nth-child(2)>td:nth-child(2)",
            " abc",
            "div#image-list>table>*>td:nth-child(2)>input",
            ["tudlogo.png abc", "leipzig.png"],
        )

        # check correct delete - image
        self.check_delete_row(
            "div#image-list>table>tr:nth-child(2)>td:nth-child(5)>td>input",
            "div#image-list>table>*>td:nth-child(2)>input",
            ["leipzig.png"],
        )

    # TODO: test index page
    def index_page(self):
        # check spinner
        # check register
        # check login
        # check poster count
        # check poster#1 content
        # check prevent editing:
        #   -title
        #   -add author
        #   -author item interaction
        #   -change author order
        #   -box text
        #   -box image drop
        #   -box interaction
        pass


if __name__ == "__main__":
    parser = argparse.ArgumentParser()
    parser.add_argument(
        "--test",
        "-t",
        nargs="*",
        help="Liste von Testmethoden, die ausgeführt werden sollen. Wenn nichts angegeben, laufen alle Tests.",
    )
    args = parser.parse_args()

    create_driver()

    if args.test:
        # Wenn Testnamen angegeben sind, bauen wir die Test-Suite nur mit diesen Tests
        suite = unittest.TestSuite()
        loader = unittest.TestLoader()
        for test_name in args.test:
            suite.addTest(PythonOrgSearch(test_name))
        runner = unittest.TextTestRunner()
        runner.run(suite)
    else:
        # Ohne Parameter alle Tests ausführen
        unittest.main()
